<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Google Gemini over its generateContent REST endpoint. Built on Laravel's HTTP client because the
 * laravel/ai SDK used by the PAF application needs Laravel 12, and its request shape is copied
 * from that SDK so the two applications ask Gemini the same way.
 */
class GeminiProvider implements AiProviderInterface
{
    public const NAME = 'gemini';

    private const RETRIES = 1;

    /** Finish reasons that mean Gemini's safety filters withheld the answer. */
    private const BLOCKED = ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST', 'SPII'];

    /**
     * @param array{key?: string|null, url?: string, model?: string, timeout?: int, thinking_level?: string|null, retry_ms?: int} $config
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

    public function setupProblem(): ?string
    {
        return blank($this->config['key'] ?? null)
            ? 'AI is not set up yet. Add GEMINI_API_KEY to the .env file.'
            : null;
    }

    public function maxDurationSeconds(): int
    {
        return (self::RETRIES + 1) * $this->timeout() + (int) ceil(($this->config['retry_ms'] ?? 0) / 1000);
    }

    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens): AiResponse
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        $model = $this->model();
        $startedAt = hrtime(true);

        $generationConfig = [
            'response_mime_type' => 'application/json',
            'response_json_schema' => $schema,
            'maxOutputTokens' => $maxOutputTokens,
        ];

        if (filled($thinkingLevel = $this->config['thinking_level'] ?? null)) {
            $generationConfig['thinkingConfig'] = ['thinkingLevel' => $thinkingLevel];
        }

        $parts = array_map(fn (AiDocument $document): array => [
            'inlineData' => ['mimeType' => $document->mimeType, 'data' => base64_encode($document->contents)],
        ], $documents);
        $parts[] = ['text' => $prompt];

        try {
            $response = Http::baseUrl(rtrim((string) ($this->config['url'] ?? ''), '/'))
                ->withHeaders(['x-goog-api-key' => $this->config['key']])
                ->timeout($this->timeout())
                ->retry(self::RETRIES + 1, (int) ($this->config['retry_ms'] ?? 0), fn (Throwable $e): bool => $this->isTransient($e), false)
                ->post("models/{$model}:generateContent", [
                    'system_instruction' => ['parts' => [['text' => $instructions]]],
                    'contents' => [['role' => 'user', 'parts' => $parts]],
                    'generationConfig' => $generationConfig,
                ]);
        } catch (ConnectionException $e) {
            throw new AiUnavailableException('The AI service could not be reached. Try again in a minute.', 0, $e);
        }

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return $this->responseFrom((array) $response->json(), $model, (int) ((hrtime(true) - $startedAt) / 1_000_000));
    }

    /**
     * Thinking tokens are billed as output, so they are counted with the output tokens.
     *
     * @param array<string, mixed> $data
     */
    private function responseFrom(array $data, string $model, int $durationMs): AiResponse
    {
        $usage = $data['usageMetadata'] ?? [];
        $input = (int) ($usage['promptTokenCount'] ?? 0);
        $output = (int) ($usage['candidatesTokenCount'] ?? 0) + (int) ($usage['thoughtsTokenCount'] ?? 0);
        $finishReason = $data['candidates'][0]['finishReason'] ?? null;

        $text = collect($data['candidates'][0]['content']['parts'] ?? [])
            ->reject(fn (array $part): bool => (bool) ($part['thought'] ?? false))
            ->pluck('text')
            ->filter()
            ->implode('');

        return new AiResponse(
            text: $text,
            provider: self::NAME,
            model: (string) ($data['modelVersion'] ?? $model),
            inputTokens: $input,
            outputTokens: $output,
            totalTokens: (int) ($usage['totalTokenCount'] ?? $input + $output),
            durationMs: $durationMs,
            cutOff: $finishReason === 'MAX_TOKENS',
            refused: \in_array($finishReason, self::BLOCKED, true) || filled($data['promptFeedback']['blockReason'] ?? null),
        );
    }

    private function timeout(): int
    {
        return (int) ($this->config['timeout'] ?? 180);
    }

    /**
     * Gemini is often briefly overloaded; one retry after a short pause usually gets through.
     */
    private function isTransient(Throwable $e): bool
    {
        return $e instanceof ConnectionException
            || ($e instanceof RequestException && ($e->response->serverError() || $e->response->status() === 429));
    }

    private function failureMessage(Response $response): string
    {
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
