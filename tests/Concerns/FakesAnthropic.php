<?php

namespace Tests\Concerns;

use App\Services\Ai\Providers\AnthropicProvider;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Fakes the Claude API at the HTTP level. The Anthropic SDK sends requests through its own PSR-18
 * client, which Http::fake() does not see, so a mocked Guzzle client is bound as its transporter.
 */
trait FakesAnthropic
{
    /** @var array<int, array{request: Request, response: ResponseInterface|null}> */
    protected array $anthropicRequests = [];

    /**
     * @return array<string, mixed>
     */
    protected function anthropicConfig(): array
    {
        return [
            'key'         => 'test-anthropic-key',
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
     * Queues replies (or exceptions) for the Claude API and records each request sent.
     *
     * @param array<int, ResponseInterface|Throwable> $replies
     */
    protected function fakeAnthropic(array $replies): GuzzleClient
    {
        $this->anthropicRequests = [];

        $stack = HandlerStack::create(new MockHandler($replies));
        $stack->push(Middleware::history($this->anthropicRequests));

        $client = new GuzzleClient(['handler' => $stack]);

        $this->app->when(AnthropicProvider::class)
            ->needs(\Psr\Http\Client\ClientInterface::class)
            ->give(fn () => $client);

        return $client;
    }

    /**
     * @param array<string, mixed> $json The structured answer Claude returns
     */
    protected function claudeReply(array $json, string $stopReason = 'end_turn', int $input = 900, int $output = 250, int $cacheRead = 0): Response
    {
        return new Response(200, ['content-type' => 'application/json'], json_encode([
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
        ]));
    }

    protected function claudeError(int $status, string $type, string $message): Response
    {
        return new Response($status, ['content-type' => 'application/json'], json_encode([
            'type'  => 'error',
            'error' => ['type' => $type, 'message' => $message],
        ]));
    }

    /**
     * The JSON body of the n-th request sent to the Claude API.
     *
     * @return array<string, mixed>
     */
    protected function anthropicRequestBody(int $index = 0): array
    {
        return json_decode((string) $this->anthropicRequests[$index]['request']->getBody(), true);
    }
}
