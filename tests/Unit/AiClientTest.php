<?php

namespace Tests\Unit;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiResponse;
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

    public function test_the_chain_follows_ai_provider_then_ai_failover_with_aliases_and_no_repeats(): void
    {
        config(['ai.provider' => 'Claude', 'ai.failover' => ' google , anthropic,gemini']);

        $this->assertSame(['anthropic', 'gemini'], $this->client()->configuredNames());
        $this->assertSame(['Claude', 'Gemini'], array_map(fn (AiProviderInterface $p): string => $p->label(), $this->client()->chain()));
    }

    public function test_an_unknown_provider_is_reported_instead_of_ignored(): void
    {
        config(['ai.failover' => 'mistral']);

        $this->assertSame('Unknown AI provider "mistral" in AI_PROVIDER or AI_FAILOVER. Use gemini, anthropic or openai.', $this->client()->setupProblem());
    }

    public function test_ai_can_be_turned_off_even_with_keys(): void
    {
        config(['ai.enabled' => false]);

        $this->assertSame('AI is turned off. Set AI_ENABLED=true in the .env file.', $this->client()->setupProblem());
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
                . 'Claude: The AI service is busy or its quota is used up. Try again later.',
                $e->getMessage()
            );
        }

        $this->assertSame(['gemini', 'anthropic'], array_column($this->failures, 0));
    }

    public function test_with_one_provider_its_own_message_is_kept(): void
    {
        config(['ai.failover' => '']);
        $this->geminiDown();

        $this->expectException(AiUnavailableException::class);
        $this->expectExceptionMessage('The AI service is having problems. Try again in a minute.');

        $this->generate();
    }
}
