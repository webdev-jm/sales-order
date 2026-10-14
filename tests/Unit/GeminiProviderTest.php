<?php

namespace Tests\Unit;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use App\Services\Ai\Providers\GeminiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesGemini;
use Tests\TestCase;

class GeminiProviderTest extends TestCase
{
    use FakesGemini;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureGemini();
    }

    private function provider(): GeminiProvider
    {
        return new GeminiProvider(config('ai.providers.gemini'));
    }

    private function generate(array $documents = [], array $schema = ['type' => 'object']): AiResponse
    {
        return $this->provider()->generateJson('Be exact.', 'Read this.', $documents, $schema, 1000);
    }

    public function test_it_sends_the_instructions_documents_prompt_and_schema_to_the_configured_model(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);

        $schema = ['type' => 'object', 'properties' => ['ok' => ['type' => 'boolean']]];

        $this->generate([AiDocument::pdf('%PDF-1.4')], $schema);

        Http::assertSent(function (Request $request) use ($schema): bool {
            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-test:generateContent'
                && $request->header('x-goog-api-key') === ['test-key']
                && data_get($request->data(), 'system_instruction.parts.0.text') === 'Be exact.'
                && data_get($request->data(), 'contents.0.parts.0.inlineData') === ['mimeType' => 'application/pdf', 'data' => base64_encode('%PDF-1.4')]
                && data_get($request->data(), 'contents.0.parts.1.text') === 'Read this.'
                && data_get($request->data(), 'generationConfig.response_mime_type') === 'application/json'
                && data_get($request->data(), 'generationConfig.response_json_schema') === $schema
                && data_get($request->data(), 'generationConfig.maxOutputTokens') === 1000
                && data_get($request->data(), 'generationConfig.thinkingConfig') === ['thinkingLevel' => 'low'];
        });
    }

    public function test_a_blank_thinking_level_leaves_the_model_default(): void
    {
        config(['ai.providers.gemini.thinking_level' => '']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);

        $this->generate();

        Http::assertSent(fn (Request $request): bool => !array_key_exists('thinkingConfig', $request->data()['generationConfig']));
    }

    public function test_it_reads_the_reply_and_counts_thinking_tokens_as_output(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true], 'STOP', 1000, 200, 30))]);

        $response = $this->generate();

        $this->assertSame(['ok' => true], $response->json());
        $this->assertSame('gemini', $response->provider);
        $this->assertSame('gemini-test-001', $response->model);
        $this->assertSame(1000, $response->inputTokens);
        $this->assertSame(230, $response->outputTokens);
        $this->assertSame(1230, $response->totalTokens);
        $this->assertFalse($response->wasCutOff());
        $this->assertFalse($response->wasRefused());
    }

    public function test_thought_parts_are_left_out_of_the_reply_text(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [
                ['text' => 'Let me think…', 'thought' => true],
                ['text' => '{"a":1}'],
            ]], 'finishReason' => 'STOP']],
        ])]);

        $response = $this->generate();

        $this->assertSame(['a' => 1], $response->json());
        $this->assertSame('gemini-test', $response->model, 'Without modelVersion the configured model is reported.');
    }

    public function test_cut_off_and_blocked_replies_are_flagged(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push($this->geminiReply(['ok' => true], 'MAX_TOKENS'))
            ->push($this->geminiReply([], 'SAFETY')),
        ]);

        $this->assertTrue($this->generate()->wasCutOff());
        $this->assertTrue($this->generate()->wasRefused());
    }

    public function test_it_refuses_to_call_out_without_an_api_key(): void
    {
        config(['ai.providers.gemini.key' => null]);
        Http::fake();

        $this->assertSame('AI is not set up yet. Add GEMINI_API_KEY to the .env file.', $this->provider()->setupProblem());

        try {
            $this->generate();
            $this->fail('Expected an AiUnavailableException.');
        } catch (AiUnavailableException $e) {
            $this->assertStringContainsString('GEMINI_API_KEY', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_an_overloaded_reply_is_retried_once(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
            ->push(['error' => ['message' => 'overloaded']], 503)
            ->push($this->geminiReply(['ok' => true])),
        ]);

        $this->assertSame(['ok' => true], $this->generate()->json());
        Http::assertSentCount(2);
    }

    public function test_a_rate_limit_reply_becomes_a_friendly_message(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Quota exceeded']], 429)]);

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('busy or its quota is used up');

        $this->generate();
    }

    public function test_a_bad_request_passes_the_provider_reason_on_and_is_not_retried(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Invalid schema']], 400)]);

        try {
            $this->generate();
            $this->fail('Expected an AiUnavailableException.');
        } catch (AiUnavailableException $e) {
            $this->assertSame('The AI service refused the request: Invalid schema', $e->getMessage());
        }

        Http::assertSentCount(1);
    }

    public function test_a_connection_failure_becomes_a_friendly_message(): void
    {
        Http::fake(fn () => throw new ConnectionException('timed out'));

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('could not be reached');

        $this->generate();
    }

    public function test_response_json_tolerates_code_fences_and_rejects_non_json(): void
    {
        $fenced = new AiResponse("```json\n{\"a\":1}\n```", 'gemini', 'm', 0, 0, 0, 0);
        $broken = new AiResponse('{"a":', 'gemini', 'm', 0, 0, 0, 0, cutOff: true);

        $this->assertSame(['a' => 1], $fenced->json());
        $this->assertNull($broken->json());
        $this->assertTrue($broken->wasCutOff());
    }

    public function test_max_duration_covers_the_retry(): void
    {
        config(['ai.providers.gemini.timeout' => 100, 'ai.providers.gemini.retry_ms' => 2500]);

        $this->assertSame(203, $this->provider()->maxDurationSeconds());
    }
}
