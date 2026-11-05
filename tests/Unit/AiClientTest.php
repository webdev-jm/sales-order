<?php

namespace Tests\Unit;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiQuota;
use App\Services\Ai\AiResponse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesAnthropic;
use Tests\Concerns\FakesGemini;
use Tests\TestCase;

class AiClientTest extends TestCase
{
    use FakesAnthropic;
    use FakesGemini;

    /** @var array<int, array{0: string, 1: string}> provider name and message of each failed attempt */
    private array $failures = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureGemini();
        $this->configureAnthropic();
        config(['ai.provider' => 'gemini', 'ai.failover' => 'claude']);
    }

    private function client(): AiClient
    {
        return app(AiClient::class);
    }

    private function generate(): AiResponse
    {
        return $this->client()->generateJson('Be exact.', 'Read this.', [], ['type' => 'object', 'properties' => []], 1000,
            function (AiProviderInterface $provider, AiUnavailableException $e): void {
                $this->failures[] = [$provider->name(), $e->getMessage()];
            });
    }

    private function geminiDown(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'overloaded']], 503)]);
    }

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public function test_the_chain_follows_the_default_provider_then_its_failover_with_aliases_and_no_repeats(): void
    {
        config(['ai.provider' => 'Claude', 'ai.failover' => ' google , anthropic,gemini']);

        $this->assertSame(['anthropic', 'gemini'], $this->client()->configuredNames());
        $this->assertSame(['Claude', 'Gemini'], array_map(fn (AiProviderInterface $p): string => $p->label(), $this->client()->chain()));
    }

    public function test_an_unknown_provider_is_reported_instead_of_ignored(): void
    {
        config(['ai.failover' => 'mistral']);

        $this->assertSame('Unknown AI provider "mistral" in config/ai.php. Use gemini, anthropic, openai, groq or qwen.', $this->client()->setupProblem());
    }

    public function test_ai_can_be_turned_off_even_with_keys(): void
    {
        config(['ai.enabled' => false]);

        $this->assertSame('AI is turned off in config/ai.php.', $this->client()->setupProblem());
    }

    public function test_without_any_key_the_main_providers_setup_problem_is_shown(): void
    {
        config(['ai.providers.gemini.key' => null, 'ai.providers.anthropic.key' => null]);
        Http::fake();

        $this->assertSame('AI is not set up yet. Add GEMINI_API_KEY to the .env file.', $this->client()->setupProblem());

        try {
            $this->generate();
            $this->fail('Expected an AiUnavailableException.');
        } catch (AiUnavailableException) {
        }

        Http::assertNothingSent();
    }

    public function test_a_provider_without_a_key_is_skipped(): void
    {
        config(['ai.providers.gemini.key' => null]);
        $this->fakeAnthropic([$this->claudeReply(['ok' => true])]);

        $this->assertNull($this->client()->setupProblem());
        $this->assertSame(['anthropic'], array_map(fn (AiProviderInterface $p): string => $p->name(), $this->client()->availableProviders()));

        $response = $this->generate();

        $this->assertSame('anthropic', $response->provider);
        $this->assertSame([], $this->failures, 'Skipping a provider without a key is not a failed call.');
        Http::assertNotSent(fn (\Illuminate\Http\Client\Request $request): bool => str_contains($request->url(), 'googleapis.com'));
    }

    public function test_the_longest_wait_adds_up_every_ready_provider(): void
    {
        config(['ai.providers.gemini.timeout' => 100, 'ai.providers.gemini.retry_ms' => 0]);

        $this->assertSame(200 + 30, $this->client()->maxDurationSeconds());
    }

    // -------------------------------------------------------------------------
    // Failover
    // -------------------------------------------------------------------------

    public function test_the_main_provider_answers_and_the_failover_is_not_called(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);

        $response = $this->generate();

        $this->assertSame('gemini', $response->provider);
        $this->assertSame([], $this->failures);
        Http::assertNotSent(fn (\Illuminate\Http\Client\Request $request): bool => str_contains($request->url(), 'api.anthropic.com'));
    }

    public function test_when_the_main_provider_fails_the_request_goes_to_the_failover(): void
    {
        $this->geminiDown();
        $this->fakeAnthropic([$this->claudeReply(['ok' => true])]);

        $response = $this->generate();

        $this->assertSame('anthropic', $response->provider);
        $this->assertSame(['ok' => true], $response->json());
        $this->assertSame([['gemini', 'The AI service is having problems. Try again in a minute.']], $this->failures);
        Http::assertSent(fn (\Illuminate\Http\Client\Request $request): bool => str_contains($request->url(), 'api.anthropic.com/v1/messages')
            && data_get($request->data(), 'messages.0.content.0.text') === 'Read this.');
    }

    public function test_failover_works_the_other_way_round_too(): void
    {
        config(['ai.provider' => 'claude', 'ai.failover' => 'gemini']);
        $this->fakeAnthropic([$this->claudeError(429, 'rate_limit_error', 'slow down')]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);

        $this->assertSame('gemini', $this->generate()->provider);
        $this->assertSame('anthropic', $this->failures[0][0]);
    }

    public function test_when_every_provider_fails_each_reason_is_reported(): void
    {
        $this->geminiDown();
        $this->fakeAnthropic([$this->claudeError(429, 'rate_limit_error', 'slow down')]);

        try {
            $this->generate();
            $this->fail('Expected an AiUnavailableException.');
        } catch (AiUnavailableException $e) {
            $this->assertSame(
                'No AI provider could answer. Gemini: The AI service is having problems. Try again in a minute. '
                . 'Claude: Claude is getting too many requests right now. Try again in a minute.',
                $e->getMessage()
            );
        }

        $this->assertSame(['gemini', 'anthropic'], array_column($this->failures, 0));
    }

    public function test_a_provider_whose_quota_is_used_up_is_skipped_until_it_resets(): void
    {
        AiQuota::block('gemini', now()->addHours(3), 'Gemini\'s daily request limit is used up.');
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);
        $this->fakeAnthropic([$this->claudeReply(['ok' => true])]);

        $this->assertSame('anthropic', $this->generate()->provider);
        Http::assertNotSent(fn (\Illuminate\Http\Client\Request $request): bool => str_contains($request->url(), 'generativelanguage'));

        $this->travel(4)->hours();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);

        $this->assertSame('gemini', $this->generate()->provider);
    }

    public function test_when_every_provider_is_out_of_quota_the_reasons_are_the_setup_problem(): void
    {
        config(['ai.failover' => '']);
        AiQuota::block('gemini', now()->addHours(3), 'Gemini\'s daily request limit is used up. It resets at 4:00 PM.');
        Http::fake();

        $this->assertSame(
            'Gemini\'s daily request limit is used up. It resets at 4:00 PM. Add a failover provider on the AI Settings page to keep working until then.',
            $this->client()->setupProblem()
        );

        $this->expectException(AiUnavailableException::class);
        $this->generate();
    }

    public function test_with_one_provider_its_own_message_is_kept(): void
    {
        config(['ai.failover' => '']);
        $this->geminiDown();

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('The AI service is having problems. Try again in a minute.');

        $this->generate();
    }

    // -------------------------------------------------------------------------
    // Providers that cannot read the attached file
    // -------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $json
     * @return array<string, mixed>
     */
    private function chatCompletion(array $json): array
    {
        return [
            'model'   => 'openai/gpt-oss-120b',
            'choices' => [['message' => ['role' => 'assistant', 'content' => json_encode($json)], 'finish_reason' => 'stop']],
            'usage'   => ['prompt_tokens' => 50, 'completion_tokens' => 10, 'total_tokens' => 60],
        ];
    }

    private function generatePdf(): AiResponse
    {
        return $this->client()->generateJson('Be exact.', 'Read this.', [AiDocument::pdf('%PDF-1.4', 'po.pdf')], ['type' => 'object', 'properties' => []], 1000,
            function (AiProviderInterface $provider, AiUnavailableException $e): void {
                $this->failures[] = [$provider->name(), $e->getMessage()];
            });
    }

    public function test_a_pdf_skips_a_text_only_main_provider_and_goes_to_the_backup_that_reads_pdfs(): void
    {
        config(['ai.provider' => 'groq', 'ai.failover' => 'gemini', 'ai.providers.groq.key' => 'test-groq-key']);
        Http::fake([
            'api.groq.com/*'                       => Http::response($this->chatCompletion(['from' => 'groq'])),
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['from' => 'gemini'])),
        ]);

        $response = $this->generatePdf();

        $this->assertSame('gemini', $response->provider);
        $this->assertSame([], $this->failures);
        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.groq.com'));
    }

    public function test_a_request_without_files_goes_to_a_text_only_main_provider(): void
    {
        config(['ai.provider' => 'groq', 'ai.failover' => 'gemini', 'ai.providers.groq.key' => 'test-groq-key']);
        Http::fake(['api.groq.com/*' => Http::response($this->chatCompletion(['from' => 'groq']))]);

        $response = $this->generate();

        $this->assertSame('groq', $response->provider);
        $this->assertSame(['from' => 'groq'], $response->json());
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && $request->header('Authorization') === ['Bearer test-groq-key']
            && $request->data()['reasoning_effort'] === 'low');
    }

    public function test_with_only_text_only_providers_on_a_pdf_says_which_providers_read_pdfs(): void
    {
        config([
            'ai.provider'           => 'groq',
            'ai.failover'           => 'qwen',
            'ai.providers.groq.key' => 'test-groq-key',
            'ai.providers.qwen.key' => 'test-qwen-key',
        ]);
        Http::fake();

        $this->assertNull($this->client()->setupProblem());

        try {
            $this->generatePdf();
            $this->fail('A PDF was sent to a provider that cannot read it.');
        } catch (AiUnavailableException $e) {
            $this->assertSame('None of the AI providers turned on can read PDF files. Turn on Gemini, Claude or OpenAI on the AI Settings page.', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_only_providers_that_read_every_file_are_offered(): void
    {
        config([
            'ai.provider'           => 'groq',
            'ai.failover'           => 'gemini,qwen',
            'ai.providers.groq.key' => 'test-groq-key',
            'ai.providers.qwen.key' => 'test-qwen-key',
        ]);

        $names = fn (array $documents): array => array_map(fn (AiProviderInterface $p): string => $p->name(), $this->client()->providersReading($documents));

        $this->assertSame(['groq', 'gemini', 'qwen'], $names([]));
        $this->assertSame(['gemini'], $names([AiDocument::pdf('%PDF-1.4')]));
        $this->assertSame(['groq', 'gemini', 'qwen'], $names([new AiDocument('image/png', 'png')]));
        $this->assertSame(['gemini'], $names([new AiDocument('image/png', 'png'), AiDocument::pdf('%PDF-1.4')]));
    }
}
