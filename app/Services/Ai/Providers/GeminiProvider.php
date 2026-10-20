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
 * Google Gemini. Replies are generated through laravel/ai (Gemini's Interactions API); the model
 * list and connection check use the REST models endpoint directly, since they cost no tokens and
 * laravel/ai has no call for them.
 */
class GeminiProvider implements AiProviderInterface
{
    use GeneratesJsonWithLaravelAi;

    public const NAME = 'gemini';

    /** Seconds a model lookup or connection check may take. */
    private const CHECK_TIMEOUT = 20;

    /**
     * @param array{key?: string|null, url?: string, model?: string, timeout?: int, effort?: string|null, retry_ms?: int} $config
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
        return 'Gemini';
    }

    public function model(): string
    {
        return (string) ($this->config['model'] ?? '');
    }

    public function effortLevels(): array
    {
        return ['minimal', 'low', 'medium', 'high'];
    }

    public function setupProblem(): ?string
    {
        return blank($this->config['key'] ?? null)
            ? 'AI is not set up yet. Add GEMINI_API_KEY to the .env file.'
            : null;
    }

    public function maxDurationSeconds(): int
    {
        return 2 * $this->timeoutSeconds() + (int) ceil($this->retryDelayMs() / 1000);
    }

    public function timeoutSeconds(): int
    {
        return (int) ($this->config['timeout'] ?? 180);
    }

    public function laravelAiConfig(): array
    {
        return [
            'driver' => 'gemini',
            'key'    => $this->config['key'] ?? null,
            'url'    => $this->config['url'] ?? null,
        ];
    }

    /**
     * How much Gemini thinks before it answers. Thinking tokens are billed as output.
     */
    public function laravelAiOptions(): array
    {
        return filled($level = $this->config['effort'] ?? null)
            ? ['generation_config' => ['thinking_level' => $level]]
            : [];
    }

    public function checkConnection(): string
    {
        $this->ensureReady();

        $response = $this->send(fn () => $this->request(self::CHECK_TIMEOUT)->get('models/' . $this->model()));

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return 'Connected. ' . $response->json('displayName', $this->model()) . ' (' . $this->model() . ') is ready.';
    }

    public function listModels(): array
    {
        $this->ensureReady();

        $response = $this->send(fn () => $this->request(self::CHECK_TIMEOUT)->get('models', ['pageSize' => 1000]));

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return collect($response->json('models', []))
            ->filter(fn (array $model): bool => \in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true))
            ->map(fn (array $model): string => Str::after((string) $model['name'], 'models/'))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Gemini is often briefly overloaded; one retry after a short pause usually gets through.
     */
    private function retryCount(): int
    {
        return 1;
    }

    private function retryDelayMs(): int
    {
        return (int) ($this->config['retry_ms'] ?? 0);
    }

    private function ensureReady(): void
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }
    }

    private function request(int $timeout): PendingRequest
    {
        return Http::baseUrl(rtrim((string) ($this->config['url'] ?? ''), '/'))
            ->withHeaders(['x-goog-api-key' => $this->config['key']])
            ->timeout($timeout);
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

    /**
     * Gemini answers a bad key with 400 and the reason API_KEY_INVALID, or with 403.
     */
    private function failureMessage(Response $response): string
    {
        $reasons = collect($response->json('error.details', []))->pluck('reason');

        if ($response->status() === 403 || $reasons->contains('API_KEY_INVALID')) {
            return 'The AI service rejected the API key. Check GEMINI_API_KEY in the .env file.';
        }

        if ($response->status() === 404) {
            return 'The AI model "' . $this->model() . '" was not found. Choose another model on the AI Settings page.';
        }

        if ($response->status() === 429) {
            return 'The AI service is busy or its quota is used up. Try again later.';
        }

        if ($response->serverError()) {
            return 'The AI service is having problems. Try again in a minute.';
        }

        return 'The AI service refused the request: '
            . Str::limit((string) $response->json('error.message', $response->reason()), 200);
    }
}
