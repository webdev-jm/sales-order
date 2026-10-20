<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Services\Ai\Concerns\GeneratesJsonWithLaravelAi;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Anthropic Claude. Replies are generated through laravel/ai (Messages API with structured
 * output); the model list and connection check call the Models API directly, since they cost no
 * tokens and laravel/ai has no call for them.
 */
class AnthropicProvider implements AiProviderInterface
{
    use GeneratesJsonWithLaravelAi;

    public const NAME = 'anthropic';

    private const API_VERSION = '2023-06-01';

    /** Seconds a model lookup or connection check may take. */
    private const CHECK_TIMEOUT = 20;

    /**
     * @param array{key?: string|null, url?: string, model?: string, timeout?: int, effort?: string|null, max_retries?: int, retry_ms?: int} $config
     */
    public function __construct(private array $config)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function label(): string
    {
        return 'Claude';
    }

    public function model(): string
    {
        return (string) ($this->config['model'] ?? '');
    }

    public function effortLevels(): array
    {
        return ['low', 'medium', 'high', 'xhigh', 'max'];
    }

    public function setupProblem(): ?string
    {
        return blank($this->config['key'] ?? null)
            ? 'AI is not set up yet. Add ANTHROPIC_API_KEY to the .env file.'
            : null;
    }

    public function maxDurationSeconds(): int
    {
        $retries = $this->retryCount();

        return ($retries + 1) * $this->timeoutSeconds() + $retries * (int) ceil($this->retryDelayMs() / 1000);
    }

    public function timeoutSeconds(): int
    {
        return (int) ($this->config['timeout'] ?? 180);
    }

    public function laravelAiConfig(): array
    {
        return [
            'driver' => 'anthropic',
            'key'    => $this->config['key'] ?? null,
            'url'    => $this->baseUrl(),
        ];
    }

    /**
     * Claude's effort level: how much it thinks and spends.
     */
    /**
     * Claude's effort level, and Anthropic's automatic prompt caching: one cache breakpoint after the last block
     * of the request, so each step of a tool loop and each follow-up reads the earlier part from the cache.
     */
    public function laravelAiOptions(): array
    {
        $options = ['cache_control' => ['type' => 'ephemeral']];

        if (filled($effort = $this->config['effort'] ?? null)) {
            $options['output_config'] = ['effort' => $effort];
        }

        return $options;
    }

    public function checkConnection(): string
    {
        $this->ensureReady();

        $response = $this->send(fn () => $this->request()->get('models/' . $this->model()));

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return 'Connected. ' . $response->json('display_name', $this->model()) . ' (' . $response->json('id', $this->model()) . ') is ready.';
    }

    public function listModels(): array
    {
        $this->ensureReady();

        $response = $this->send(fn () => $this->request()->get('models', ['limit' => 1000]));

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return collect($response->json('data', []))->pluck('id')->filter()->map(fn ($id): string => (string) $id)->sort()->values()->all();
    }

    private function retryCount(): int
    {
        return max(0, (int) ($this->config['max_retries'] ?? 1));
    }

    private function retryDelayMs(): int
    {
        return (int) ($this->config['retry_ms'] ?? 500);
    }

    private function baseUrl(): string
    {
        return rtrim((string) ($this->config['url'] ?? 'https://api.anthropic.com/v1'), '/');
    }

    private function ensureReady(): void
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withHeaders(['x-api-key' => (string) $this->config['key'], 'anthropic-version' => self::API_VERSION])
            ->acceptJson()
            ->timeout(self::CHECK_TIMEOUT);
    }

    /**
     * @param callable(): Response $call
     */
    private function send(callable $call): Response
    {
        try {
            return $call();
        } catch (ConnectionException $e) {
            throw new AiUnavailableException('The AI service could not be reached. Try again in a minute.', 0, $e);
        }
    }

    private function failureMessage(Response $response): string
    {
        if (\in_array($response->status(), [401, 403], true)) {
            return 'The AI service rejected the API key. Check ANTHROPIC_API_KEY in the .env file.';
        }

        if ($response->status() === 404) {
            return 'The AI model "' . $this->model() . '" was not found. Choose another model on the AI Settings page.';
        }

        if ($response->status() === 429) {
            return 'The AI service is busy or its quota is used up. Try again later.';
        }

        if ($response->serverError() || $response->status() === 529) {
            return 'The AI service is having problems. Try again in a minute.';
        }

        return 'The AI service refused the request: '
            . Str::limit((string) $response->json('error.message', $response->reason()), 200);
    }
}
