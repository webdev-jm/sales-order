<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Providers\AnthropicProvider;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesAnthropic;
use Tests\TestCase;

class AnthropicProviderTest extends TestCase
{
    use FakesAnthropic;

    private function provider(array $overrides = []): AnthropicProvider
    {
        return new AnthropicProvider(array_merge($this->anthropicConfig(), $overrides));
    }

    private function generate(array $documents = [], array $schema = ['type' => 'object', 'properties' => []], array $config = []): AiResponse
    {
        return $this->provider($config)->generateJson('Be exact.', 'Read this.', $documents, $schema, 1000);
    }

    public function test_it_sends_the_pdf_prompt_schema_and_effort_through_laravel_ai(): void
    {
        $this->fakeAnthropic([$this->claudeReply(['ok' => true])]);

        $this->generate([AiDocument::pdf('%PDF-1.4')], [
            'type'       => 'object',
            'properties' => [
                'ok'     => ['type' => 'boolean'],
                'price'  => ['type' => ['number', 'null']],
                'orders' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => ['uom' => ['type' => 'string', 'enum' => ['CS', '']]]]],
            ],
            'required'   => ['ok'],
        ]);

        $request = $this->anthropicRequest();
        $body = $this->anthropicRequestBody();
        $schema = $body['output_config']['format']['schema'];

        $this->assertSame('https://api.anthropic.com/v1/messages', $request->url());
        $this->assertSame(['test-anthropic-key'], $request->header('x-api-key'));
        $this->assertSame('claude-test', $body['model']);
        $this->assertSame(1000, $body['max_tokens']);
        $this->assertSame('Be exact.', $body['system']);
        $this->assertEquals(
            ['type' => 'document', 'source' => ['media_type' => 'application/pdf', 'type' => 'base64', 'data' => base64_encode('%PDF-1.4')]],
            $body['messages'][0]['content'][0]
        );
        $this->assertSame(['type' => 'text', 'text' => 'Read this.'], $body['messages'][0]['content'][1]);
        $this->assertSame('json_schema', $body['output_config']['format']['type']);
        $this->assertFalse($schema['additionalProperties']);
        $this->assertFalse($schema['properties']['orders']['items']['additionalProperties'], 'Nested objects are closed too.');
        $this->assertSame(['anyOf' => [['type' => 'number'], ['type' => 'null']]], $schema['properties']['price']);
        $this->assertSame(['CS', ''], $schema['properties']['orders']['items']['properties']['uom']['enum']);
        $this->assertSame('low', $body['output_config']['effort']);
    }

    public function test_a_blank_effort_leaves_the_model_default(): void
    {
        $this->fakeAnthropic([$this->claudeReply(['ok' => true])]);

        $this->generate(config: ['effort' => '']);

        $this->assertArrayNotHasKey('effort', (array) ($this->anthropicRequestBody()['output_config'] ?? []));
    }

    public function test_images_are_sent_as_image_blocks(): void
    {
        $this->fakeAnthropic([$this->claudeReply(['ok' => true])]);

        $this->generate([new AiDocument('image/png', 'png-bytes', 'po.png')]);

        $this->assertEquals(
            ['type' => 'image', 'source' => ['data' => base64_encode('png-bytes'), 'media_type' => 'image/png', 'type' => 'base64']],
            $this->anthropicRequestBody()['messages'][0]['content'][0]
        );
    }

    public function test_an_unsupported_file_type_is_refused_before_calling_out(): void
    {
        $this->fakeAnthropic([]);

        try {
            $this->generate([new AiDocument('text/csv', 'a,b')]);
            $this->fail('Expected an AiUnavailableException.');
        } catch (AiUnavailableException $e) {
            $this->assertSame('Claude cannot read text/csv files.', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_it_reads_the_text_and_tokens_of_the_reply(): void
    {
        $this->fakeAnthropic([$this->claudeReply(['ok' => true], 'end_turn', 900, 250, 100)]);

        $response = $this->generate();

        $this->assertSame(['ok' => true], $response->json());
        $this->assertSame('anthropic', $response->provider);
        $this->assertSame('claude-test-20261001', $response->model, 'The model that answered is logged.');
        $this->assertSame(1000, $response->inputTokens, 'Cache reads count as input.');
        $this->assertSame(250, $response->outputTokens);
        $this->assertSame(1250, $response->totalTokens);
        $this->assertFalse($response->wasCutOff());
        $this->assertFalse($response->wasRefused());
    }

    public function test_max_tokens_and_refusal_stops_are_flagged(): void
    {
        $this->fakeAnthropic([$this->claudeReply(['ok' => true], 'max_tokens'), $this->claudeReply([], 'refusal')]);

        $this->assertTrue($this->generate()->wasCutOff());
        $this->assertTrue($this->generate()->wasRefused());
    }

    public function test_it_refuses_to_call_out_without_an_api_key(): void
    {
        $this->fakeAnthropic([]);
        $provider = $this->provider(['key' => null]);

        $this->assertSame('AI is not set up yet. Add ANTHROPIC_API_KEY to the .env file.', $provider->setupProblem());

        $this->expectException(AiUnavailableException::class);
        $provider->generateJson('x', 'y', [], ['type' => 'object'], 10);
    }

    public function test_an_overloaded_reply_is_retried(): void
    {
        $this->fakeAnthropic([$this->claudeError(529, 'overloaded_error', 'Overloaded'), $this->claudeReply(['ok' => true])]);

        $response = $this->generate(config: ['max_retries' => 1]);

        $this->assertSame(['ok' => true], $response->json());
        Http::assertSentCount(2);
    }

    /**
     * @return array<string, array{0: array{0: int, 1: string, 2: string}, 1: string}>
     */
    public static function failures(): array
    {
        return [
            'rate limited' => [[429, 'rate_limit_error', 'slow down'], 'The AI service is busy or its quota is used up. Try again later.'],
            'overloaded'   => [[529, 'overloaded_error', 'Overloaded'], 'The AI service is having problems. Try again in a minute.'],
            'bad key'      => [[401, 'authentication_error', 'invalid x-api-key'], 'The AI service rejected the API key. Check ANTHROPIC_API_KEY in the .env file.'],
            'bad model'    => [[404, 'not_found_error', 'model: claude-test'], 'The AI model "claude-test" was not found. Choose another model on the AI Settings page.'],
            'bad request'  => [[400, 'invalid_request_error', 'Schema is too complex'], 'The AI service refused the request: Schema is too complex'],
        ];
    }

    /**
     * @param array{0: int, 1: string, 2: string} $error
     */
    #[DataProvider('failures')]
    public function test_api_errors_become_friendly_messages(array $error, string $message): void
    {
        $this->fakeAnthropic([$this->claudeError(...$error)]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage($message);

        $this->generate();
    }

    public function test_a_connection_failure_becomes_a_friendly_message(): void
    {
        $this->fakeAnthropic([null]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('The AI service could not be reached. Try again in a minute.');

        $this->generate();
    }

    public function test_max_duration_covers_retries_and_pauses(): void
    {
        $this->assertSame(30, $this->provider(['max_retries' => 0, 'timeout' => 30])->maxDurationSeconds());
        $this->assertSame(62, $this->provider(['max_retries' => 1, 'timeout' => 30, 'retry_ms' => 1500])->maxDurationSeconds());
    }

    public function test_it_lists_the_models_the_key_can_use(): void
    {
        $this->fakeAnthropic([Http::response([
            'data'     => [
                ['type' => 'model', 'id' => 'claude-z', 'display_name' => 'Claude Z', 'created_at' => '2026-01-01T00:00:00Z'],
                ['type' => 'model', 'id' => 'claude-a', 'display_name' => 'Claude A', 'created_at' => '2026-01-01T00:00:00Z'],
            ],
            'has_more' => false,
        ])]);

        $this->assertSame(['claude-a', 'claude-z'], $this->provider()->listModels());
        $this->assertStringStartsWith('https://api.anthropic.com/v1/models', $this->anthropicRequest()->url());
        $this->assertSame(['2023-06-01'], $this->anthropicRequest()->header('anthropic-version'));
    }

    public function test_the_connection_check_looks_up_the_model_without_using_tokens(): void
    {
        $this->fakeAnthropic([Http::response([
            'type' => 'model', 'id' => 'claude-test', 'display_name' => 'Claude Test', 'created_at' => '2026-01-01T00:00:00Z',
        ])]);

        $this->assertSame('Connected. Claude Test (claude-test) is ready.', $this->provider()->checkConnection());
        $this->assertSame('GET', $this->anthropicRequest()->method());
        $this->assertSame('https://api.anthropic.com/v1/models/claude-test', $this->anthropicRequest()->url());
    }

    public function test_the_connection_check_names_a_bad_key(): void
    {
        $this->fakeAnthropic([$this->claudeError(401, 'authentication_error', 'invalid x-api-key')]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('The AI service rejected the API key. Check ANTHROPIC_API_KEY in the .env file.');

        $this->provider()->checkConnection();
    }
}
