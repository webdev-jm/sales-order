<?php

namespace Tests\Unit;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Providers\OpenAiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiProviderTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     */
    private function provider(array $overrides = []): OpenAiProvider
    {
        return new OpenAiProvider(array_merge([
            'key'      => 'test-openai-key',
            'url'      => 'https://api.openai.com/v1/',
            'model'    => 'gpt-test',
            'timeout'  => 30,
            'effort'   => 'low',
            'retry_ms' => 0,
        ], $overrides));
    }

    /**
     * @param array<int, AiDocument> $documents
     * @param array<string, mixed> $schema
     * @param array<string, mixed> $config
     */
    private function generate(array $documents = [], array $schema = ['type' => 'object', 'properties' => []], array $config = []): AiResponse
    {
        return $this->provider($config)->generateJson('Be exact.', 'Read this.', $documents, $schema, 1000);
    }

    /**
     * @param array<string, mixed> $json
     * @return array<string, mixed>
     */
    private function reply(array $json, string $finishReason = 'stop', int $input = 800, int $output = 200, ?string $refusal = null): array
    {
        return [
            'id'      => 'chatcmpl-test',
            'object'  => 'chat.completion',
            'model'   => 'gpt-test-2026-01-01',
            'choices' => [[
                'index'         => 0,
                'message'       => ['role' => 'assistant', 'content' => $refusal === null ? json_encode($json) : null, 'refusal' => $refusal],
                'finish_reason' => $finishReason,
            ]],
            'usage' => ['prompt_tokens' => $input, 'completion_tokens' => $output, 'total_tokens' => $input + $output],
        ];
    }

    public function test_it_sends_the_pdf_prompt_strict_schema_and_effort_to_chat_completions(): void
    {
        Http::fake(['api.openai.com/*' => Http::response($this->reply(['ok' => true]))]);

        $this->generate([AiDocument::pdf('%PDF-1.4', 'po.pdf')], [
            'type'       => 'object',
            'properties' => ['ok' => ['type' => 'boolean'], 'price' => ['type' => ['number', 'null']]],
            'required'   => ['ok'],
        ]);

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $request->url() === 'https://api.openai.com/v1/chat/completions'
                && $request->header('Authorization') === ['Bearer test-openai-key']
                && $data['model'] === 'gpt-test'
                && $data['messages'][0] === ['role' => 'system', 'content' => 'Be exact.']
                && $data['messages'][1]['content'][0] === ['type' => 'file', 'file' => ['filename' => 'po.pdf', 'file_data' => 'data:application/pdf;base64,' . base64_encode('%PDF-1.4')]]
                && $data['messages'][1]['content'][1] === ['type' => 'text', 'text' => 'Read this.']
                && $data['response_format']['type'] === 'json_schema'
                && $data['response_format']['json_schema']['strict'] === true
                && $data['response_format']['json_schema']['schema']['required'] === ['ok', 'price']
                && $data['response_format']['json_schema']['schema']['additionalProperties'] === false
                && $data['max_completion_tokens'] === 1000
                && !array_key_exists('max_tokens', $data)
                && $data['reasoning_effort'] === 'low';
        });
    }

    public function test_other_servers_get_max_tokens_and_a_blank_effort_is_left_out(): void
    {
        Http::fake(['openrouter.ai/*' => Http::response($this->reply(['ok' => true]))]);

        $this->generate([], ['type' => 'object', 'properties' => []], ['url' => 'https://openrouter.ai/api/v1', 'effort' => '']);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
            && $request->data()['max_tokens'] === 1000
            && !array_key_exists('max_completion_tokens', $request->data())
            && !array_key_exists('reasoning_effort', $request->data()));
    }

    public function test_it_reads_the_reply_and_its_tokens(): void
    {
        Http::fake(['api.openai.com/*' => Http::response($this->reply(['ok' => true], 'stop', 1000, 250))]);

        $response = $this->generate();

        $this->assertSame(['ok' => true], $response->json());
        $this->assertSame('openai', $response->provider);
        $this->assertSame('gpt-test-2026-01-01', $response->model);
        $this->assertSame(1000, $response->inputTokens);
        $this->assertSame(250, $response->outputTokens);
        $this->assertSame(1250, $response->totalTokens);
        $this->assertFalse($response->wasCutOff());
        $this->assertFalse($response->wasRefused());
    }

    public function test_a_reply_cut_off_at_the_token_cap_is_flagged(): void
    {
        Http::fake(['api.openai.com/*' => Http::response($this->reply(['ok' => true], 'length'))]);

        $this->assertTrue($this->generate()->wasCutOff());
    }

    public function test_a_refusal_is_flagged(): void
    {
        Http::fake(['api.openai.com/*' => Http::response($this->reply([], 'stop', 100, 10, 'I cannot help with that.'))]);

        $response = $this->generate();

        $this->assertTrue($response->wasRefused());
        $this->assertSame('', $response->text);
    }

    public function test_images_are_sent_as_data_urls_and_other_files_are_refused(): void
    {
        Http::fake(['api.openai.com/*' => Http::response($this->reply(['ok' => true]))]);

        $this->generate([new AiDocument('image/png', 'PNG')]);

        Http::assertSent(fn (Request $request): bool => $request->data()['messages'][1]['content'][0] === [
            'type' => 'image_url', 'image_url' => ['url' => 'data:image/png;base64,' . base64_encode('PNG')],
        ]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('OpenAI cannot read text/csv files.');

        $this->generate([new AiDocument('text/csv', 'a,b')]);
    }

    public function test_a_busy_server_is_retried_once(): void
    {
        Http::fake(['api.openai.com/*' => Http::sequence()
            ->push(['error' => ['message' => 'overloaded']], 503)
            ->push($this->reply(['ok' => true])),
        ]);

        $this->assertSame(['ok' => true], $this->generate()->json());
        Http::assertSentCount(2);
    }

    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function failures(): array
    {
        return [
            'bad key'     => [401, 'The AI service rejected the API key. Check OPENAI_API_KEY in the .env file.'],
            'bad model'   => [404, 'The AI model "gpt-test" was not found. Choose another model on the AI Settings page.'],
            'quota'       => [429, 'The AI service is busy or its quota is used up. Try again later.'],
            'server'      => [500, 'The AI service is having problems. Try again in a minute.'],
            'bad request' => [400, 'The AI service refused the request: Invalid schema.'],
        ];
    }

    /**
     * @dataProvider failures
     */
    public function test_errors_become_messages_users_can_read(int $status, string $message): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Invalid schema.']], $status)]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage($message);

        $this->generate();
    }

    public function test_an_unreachable_server_is_reported(): void
    {
        Http::fake(fn () => throw new ConnectionException('refused'));

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('The AI service could not be reached. Try again in a minute.');

        $this->generate();
    }

    public function test_without_a_key_or_address_it_says_what_to_set_and_sends_nothing(): void
    {
        Http::fake();

        $this->assertSame('AI is not set up yet. Add OPENAI_API_KEY to the .env file (any value for a local server).', $this->provider(['key' => null])->setupProblem());
        $this->assertSame('AI is not set up yet. Set the server address (OPENAI_BASE_URL) for the OpenAI-compatible provider.', $this->provider(['url' => ''])->setupProblem());

        try {
            $this->provider(['key' => ''])->listModels();
            $this->fail('No exception was thrown.');
        } catch (AiUnavailableException) {
            Http::assertNothingSent();
        }
    }

    public function test_the_label_names_well_known_servers(): void
    {
        $this->assertSame('OpenAI', $this->provider()->label());
        $this->assertSame('OpenRouter', $this->provider(['url' => 'https://openrouter.ai/api/v1/'])->label());
        $this->assertSame('OpenAI-compatible', $this->provider(['url' => 'http://localhost:11434/v1/'])->label());
    }

    public function test_it_lists_the_servers_models_sorted(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => [['id' => 'gpt-z'], ['id' => 'gpt-test'], ['id' => 'gpt-a']]])]);

        $this->assertSame(['gpt-a', 'gpt-test', 'gpt-z'], $this->provider()->listModels());
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET' && $request->header('Authorization') === ['Bearer test-openai-key']);
    }

    public function test_the_connection_check_confirms_the_model_is_on_the_server(): void
    {
        Http::fake(['api.openai.com/v1/models' => Http::response(['data' => [['id' => 'gpt-test']]])]);

        $this->assertSame('Connected. OpenAI has gpt-test ready.', $this->provider()->checkConnection());

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('Connected to OpenAI, but it has no model named "gpt-missing". Choose one from its model list.');

        $this->provider(['model' => 'gpt-missing'])->checkConnection();
    }

    public function test_the_strict_schema_requires_every_property_and_closes_nested_objects(): void
    {
        $schema = OpenAiProvider::strictSchema([
            'type'       => 'object',
            'properties' => [
                'lines' => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => ['sku' => ['type' => 'string'], 'qty' => ['type' => ['number', 'null']]],
                        'required'   => ['sku'],
                    ],
                ],
            ],
        ]);

        $this->assertSame(['lines'], $schema['required']);
        $this->assertFalse($schema['additionalProperties']);
        $this->assertSame(['sku', 'qty'], $schema['properties']['lines']['items']['required']);
        $this->assertFalse($schema['properties']['lines']['items']['additionalProperties']);
        $this->assertSame(['number', 'null'], $schema['properties']['lines']['items']['properties']['qty']['type']);
    }
}
