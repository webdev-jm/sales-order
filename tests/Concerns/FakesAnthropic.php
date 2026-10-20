<?php

namespace Tests\Concerns;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * Fakes the Claude API with Http::fake(); laravel/ai and the model calls both use Laravel's HTTP client.
 */
trait FakesAnthropic
{
    /**
     * @return array<string, mixed>
     */
    protected function anthropicConfig(): array
    {
        return [
            'driver'      => 'anthropic',
            'key'         => 'test-anthropic-key',
            'url'         => 'https://api.anthropic.com/v1',
            'model'       => 'claude-test',
            'timeout'     => 30,
            'effort'      => 'low',
            'max_retries' => 0,
            'retry_ms'    => 0,
        ];
    }

    protected function configureAnthropic(): void
    {
        config(['ai.enabled' => true, 'ai.providers.anthropic' => $this->anthropicConfig()]);
    }

    /**
     * Queues replies for the Claude API, in order. Pass null for a dropped connection.
     *
     * @param array<int, PromiseInterface|null> $replies
     */
    protected function fakeAnthropic(array $replies): void
    {
        $sequence = Http::sequence();

        foreach ($replies as $reply) {
            $reply === null ? $sequence->pushFailedConnection() : $sequence->pushResponse($reply);
        }

        Http::fake(['api.anthropic.com/*' => $sequence]);
    }

    /**
     * @param array<string, mixed> $json The structured answer Claude returns
     */
    protected function claudeReply(array $json, string $stopReason = 'end_turn', int $input = 900, int $output = 250, int $cacheRead = 0): PromiseInterface
    {
        return Http::response([
            'id'            => 'msg_test',
            'type'          => 'message',
            'role'          => 'assistant',
            'model'         => 'claude-test-20261001',
            'content'       => [
                ['type' => 'thinking', 'thinking' => '', 'signature' => 'sig'],
                ['type' => 'text', 'text' => json_encode($json)],
            ],
            'stop_reason'   => $stopReason,
            'stop_sequence' => null,
            'usage'         => [
                'input_tokens'                => $input,
                'output_tokens'               => $output,
                'cache_creation_input_tokens' => 0,
                'cache_read_input_tokens'     => $cacheRead,
            ],
        ]);
    }

    protected function claudeError(int $status, string $type, string $message): PromiseInterface
    {
        return Http::response(['type' => 'error', 'error' => ['type' => $type, 'message' => $message]], $status);
    }

    /**
     * The n-th request sent to the Claude API (requests to other providers are skipped).
     */
    protected function anthropicRequest(int $index = 0): Request
    {
        return Http::recorded(fn (Request $request): bool => str_contains($request->url(), 'api.anthropic.com'))->values()[$index][0];
    }

    /**
     * The JSON body of the n-th request sent to the Claude API.
     *
     * @return array<string, mixed>
     */
    protected function anthropicRequestBody(int $index = 0): array
    {
        return $this->anthropicRequest($index)->data();
    }
}
