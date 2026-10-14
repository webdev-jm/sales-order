<?php

namespace Tests\Unit;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Providers\AnthropicProvider;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Tests\Concerns\FakesAnthropic;
use Tests\TestCase;

class AnthropicProviderTest extends TestCase
{
    use FakesAnthropic;

    private ?ClientInterface $transport = null;

    private function provider(array $overrides = []): AnthropicProvider
    {
        return new AnthropicProvider(array_merge($this->anthropicConfig(), $overrides), $this->transport);
    }

    private function generate(array $documents = [], array $schema = ['type' => 'object', 'properties' => []], array $config = []): AiResponse
    {
        return $this->provider($config)->generateJson('Be exact.', 'Read this.', $documents, $schema, 1000);
    }

    /**
     * @param array<int, mixed> $replies
     */
    private function fake(array $replies): void
    {
        $this->transport = $this->fakeAnthropic($replies);
    }

    public function test_it_sends_the_pdf_prompt_schema_and_effort_through_the_sdk(): void
    {
        $this->fake([$this->claudeReply(['ok' => true])]);

        $this->generate([AiDocument::pdf('%PDF-1.4')], [
            'type'       => 'object',
            'properties' => ['ok' => ['type' => 'boolean'], 'price' => ['type' => ['number', 'null']]],
            'required'   => ['ok'],
        ]);

        $request = $this->anthropicRequests[0]['request'];
        $body = $this->anthropicRequestBody();

        $this->assertSame('https://api.anthropic.com/v1/messages', (string) $request->getUri());
        $this->assertSame('test-anthropic-key', $request->getHeaderLine('x-api-key'));
        $this->assertSame('claude-test', $body['model']);
        $this->assertSame(1000, $body['max_tokens']);
        $this->assertSame('Be exact.', $body['system']);
        $this->assertSame(
            ['type' => 'document', 'source' => ['media_type' => 'application/pdf', 'type' => 'base64', 'data' => base64_encode('%PDF-1.4')]],
            $body['messages'][0]['content'][0]
        );
        $this->assertSame(['type' => 'text', 'text' => 'Read this.'], $body['messages'][0]['content'][1]);
        $this->assertSame('json_schema', $body['output_config']['format']['type']);
        $this->assertFalse($body['output_config']['format']['schema']['additionalProperties']);
        $this->assertSame(
            ['anyOf' => [['type' => 'number'], ['type' => 'null']]],
            $body['output_config']['format']['schema']['properties']['price']
        );
        $this->assertSame('low', $body['output_config']['effort']);
    }

    public function test_a_blank_effort_leaves_the_model_default(): void
    {
        $this->fake([$this->claudeReply(['ok' => true])]);

        $this->generate(config: ['effort' => '']);

        $this->assertArrayNotHasKey('effort', $this->anthropicRequestBody()['output_config']);
    }

    public function test_images_are_sent_as_image_blocks(): void
    {
        $this->fake([$this->claudeReply(['ok' => true])]);

        $this->generate([new AiDocument('image/png', 'png-bytes', 'po.png')]);

        $this->assertEquals(
            ['type' => 'image', 'source' => ['data' => base64_encode('png-bytes'), 'media_type' => 'image/png', 'type' => 'base64']],
            $this->anthropicRequestBody()['messages'][0]['content'][0]
        );
    }

    public function test_an_unsupported_file_type_is_refused_before_calling_out(): void
    {
        $this->fake([]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('Claude cannot read text/csv files.');

        $this->generate([new AiDocument('text/csv', 'a,b')]);
    }

    public function test_it_reads_the_text_tokens_and_model_of_the_reply(): void
    {
        $this->fake([$this->claudeReply(['ok' => true], 'end_turn', 900, 250, 100)]);

        $response = $this->generate();

        $this->assertSame(['ok' => true], $response->json());
        $this->assertSame('anthropic', $response->provider);
        $this->assertSame('claude-test-20261001', $response->model);
        $this->assertSame(1000, $response->inputTokens, 'Cache reads count as input.');
        $this->assertSame(250, $response->outputTokens);
        $this->assertSame(1250, $response->totalTokens);
        $this->assertFalse($response->wasCutOff());
        $this->assertFalse($response->wasRefused());
    }

    public function test_max_tokens_and_refusal_stops_are_flagged(): void
    {
        $this->fake([$this->claudeReply(['ok' => true], 'max_tokens'), $this->claudeReply([], 'refusal')]);

        $this->assertTrue($this->generate()->wasCutOff());
        $this->assertTrue($this->generate()->wasRefused());
    }

    public function test_it_refuses_to_call_out_without_an_api_key(): void
    {
        $this->fake([]);
        $provider = $this->provider(['key' => null]);

        $this->assertSame('AI is not set up yet. Add ANTHROPIC_API_KEY to the .env file.', $provider->setupProblem());

        $this->expectException(AiUnavailableException::class);
        $provider->generateJson('x', 'y', [], ['type' => 'object'], 10);
    }

    public function test_an_overloaded_reply_is_retried_by_the_sdk(): void
    {
        $this->fake([$this->claudeError(529, 'overloaded_error', 'Overloaded'), $this->claudeReply(['ok' => true])]);

        $response = $this->generate(config: ['max_retries' => 1]);

        $this->assertSame(['ok' => true], $response->json());
        $this->assertCount(2, $this->anthropicRequests);
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
            'bad model'    => [[404, 'not_found_error', 'model: claude-test'], 'The AI model "claude-test" was not found. Check ANTHROPIC_MODEL in the .env file.'],
            'bad request'  => [[400, 'invalid_request_error', 'Schema is too complex'], 'The AI service refused the request: Schema is too complex'],
        ];
    }

    /**
     * @dataProvider failures
     *
     * @param array{0: int, 1: string, 2: string} $error
     */
    public function test_api_errors_become_friendly_messages(array $error, string $message): void
    {
        $this->fake([$this->claudeError(...$error)]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage($message);

        $this->generate();
    }

    public function test_a_connection_failure_becomes_a_friendly_message(): void
    {
        $this->fake([new ConnectException('timed out', new Request('POST', 'https://api.anthropic.com/v1/messages'))]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('The AI service could not be reached. Try again in a minute.');

        $this->generate();
    }

    public function test_strict_schema_closes_nested_objects_and_converts_nullable_types(): void
    {
        $strict = AnthropicProvider::strictSchema([
            'type'       => 'object',
            'properties' => [
                'orders' => ['type' => 'array', 'items' => [
                    'type'       => 'object',
                    'properties' => ['qty' => ['type' => ['number', 'null']], 'uom' => ['type' => 'string', 'enum' => ['CS', '']]],
                ]],
            ],
        ]);

        $this->assertFalse($strict['additionalProperties']);
        $this->assertFalse($strict['properties']['orders']['items']['additionalProperties']);
        $this->assertSame(['anyOf' => [['type' => 'number'], ['type' => 'null']]], $strict['properties']['orders']['items']['properties']['qty']);
        $this->assertSame(['type' => 'string', 'enum' => ['CS', '']], $strict['properties']['orders']['items']['properties']['uom']);
    }

    public function test_max_duration_covers_retries_and_back_off(): void
    {
        $this->assertSame(30, $this->provider(['max_retries' => 0, 'timeout' => 30])->maxDurationSeconds());
        $this->assertSame(68, $this->provider(['max_retries' => 1, 'timeout' => 30])->maxDurationSeconds());
    }
}
