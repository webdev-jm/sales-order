<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Any server that speaks the OpenAI Chat Completions API (OpenAI, OpenRouter, Groq, DeepSeek, a
 * local Ollama, ...), over Laravel's HTTP client. The server address is the base URL, so one
 * driver covers all of them. PDFs go as "file" content parts, which OpenAI and OpenRouter read.
 */
class OpenAiProvider implements AiProviderInterface
{
    public const NAME = 'openai';

    private const RETRIES = 1;

    /** Seconds a model lookup or connection check may take. */
    private const CHECK_TIMEOUT = 20;

    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /** Names shown for well-known servers; any other server is "OpenAI-compatible". */
    private const KNOWN_HOSTS = [
        'api.openai.com'   => 'OpenAI',
        'openrouter.ai'    => 'OpenRouter',
        'api.groq.com'     => 'Groq',
        'api.deepseek.com' => 'DeepSeek',
    ];

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
        return self::KNOWN_HOSTS[$this->host()] ?? 'OpenAI-compatible';
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
        if (blank($this->config['url'] ?? null)) {
            return 'AI is not set up yet. Set the server address (OPENAI_BASE_URL) for the OpenAI-compatible provider.';
        }

        return blank($this->config['key'] ?? null)
            ? 'AI is not set up yet. Add OPENAI_API_KEY to the .env file (any value for a local server).'
            : null;
    }

    public function maxDurationSeconds(): int
    {
        return (self::RETRIES + 1) * $this->timeout() + (int) ceil(($this->config['retry_ms'] ?? 0) / 1000);
    }

    /**
     * Not every server can look up one model, but all of them list their models.
     */
    public function checkConnection(): string
    {
        $models = $this->listModels();

        if (! \in_array($this->model(), $models, true)) {
            throw new AiUnavailableException('Connected to ' . $this->label() . ', but it has no model named "' . $this->model() . '". Choose one from its model list.');
        }

        return 'Connected. ' . $this->label() . ' has ' . $this->model() . ' ready.';
    }

    public function listModels(): array
    {
        $this->ensureReady();

        $response = $this->send(fn () => $this->request(self::CHECK_TIMEOUT)->get('models'));

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return collect($response->json('data', []))->pluck('id')->filter()->map(fn ($id): string => (string) $id)->sort()->values()->all();
    }

    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens): AiResponse
    {
        $this->ensureReady();

        $content = array_map(fn (AiDocument $document): array => $this->partFor($document), $documents);
        $content[] = ['type' => 'text', 'text' => $prompt];

        $payload = [
            'model'    => $this->model(),
            'messages' => [
                ['role' => 'system', 'content' => $instructions],
                ['role' => 'user', 'content' => $content],
            ],
            'response_format' => [
                'type'        => 'json_schema',
                'json_schema' => ['name' => 'reply', 'strict' => true, 'schema' => self::strictSchema($schema)],
            ],
            $this->maxTokensField() => $maxOutputTokens,
        ];

        if (filled($effort = $this->config['effort'] ?? null)) {
            $payload['reasoning_effort'] = $effort;
        }

        $startedAt = hrtime(true);

        $response = $this->send(fn () => $this->request($this->timeout())
            ->retry(self::RETRIES + 1, (int) ($this->config['retry_ms'] ?? 0), fn (Throwable $e): bool => $this->isTransient($e), false)
            ->post('chat/completions', $payload));

        if ($response->failed()) {
            throw new AiUnavailableException($this->failureMessage($response));
        }

        return $this->responseFrom((array) $response->json(), (int) ((hrtime(true) - $startedAt) / 1_000_000));
    }

    /**
     * OpenAI's strict structured outputs need every object closed with additionalProperties false
     * and every property listed as required; an optional value is answered as an empty string.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public static function strictSchema(array $schema): array
    {
        if (($schema['type'] ?? null) === 'object') {
            $schema['additionalProperties'] = false;
            $schema['properties'] = array_map(fn (array $property): array => self::strictSchema($property), $schema['properties'] ?? []);
            $schema['required'] = array_keys($schema['properties']);
        }

        if (isset($schema['items']) && \is_array($schema['items'])) {
            $schema['items'] = self::strictSchema($schema['items']);
        }

        foreach (['anyOf', 'allOf'] as $keyword) {
            if (isset($schema[$keyword])) {
                $schema[$keyword] = array_map(fn (array $branch): array => self::strictSchema($branch), $schema[$keyword]);
            }
        }

        return $schema;
    }

    /**
     * Reasoning tokens are part of completion_tokens, so they are counted as output.
     *
     * @param array<string, mixed> $data
     */
    private function responseFrom(array $data, int $durationMs): AiResponse
    {
        $choice = $data['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $finishReason = $choice['finish_reason'] ?? null;
        $input = (int) data_get($data, 'usage.prompt_tokens', 0);
        $output = (int) data_get($data, 'usage.completion_tokens', 0);

        return new AiResponse(
            text: \is_string($message['content'] ?? null) ? $message['content'] : '',
            provider: self::NAME,
            model: (string) ($data['model'] ?? $this->model()),
            inputTokens: $input,
            outputTokens: $output,
            totalTokens: (int) data_get($data, 'usage.total_tokens', $input + $output),
            durationMs: $durationMs,
            cutOff: $finishReason === 'length',
            refused: $finishReason === 'content_filter' || filled($message['refusal'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function partFor(AiDocument $document): array
    {
        $dataUrl = 'data:' . $document->mimeType . ';base64,' . base64_encode($document->contents);

        if ($document->mimeType === 'application/pdf') {
            return ['type' => 'file', 'file' => ['filename' => $document->name !== '' ? $document->name : 'document.pdf', 'file_data' => $dataUrl]];
        }

        if (\in_array($document->mimeType, self::IMAGE_TYPES, true)) {
            return ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]];
        }

        throw new AiUnavailableException($this->label() . ' cannot read ' . $document->mimeType . ' files.');
    }

    /**
     * OpenAI's reasoning models only take max_completion_tokens; most other servers only know max_tokens.
     */
    private function maxTokensField(): string
    {
        return $this->host() === 'api.openai.com' ? 'max_completion_tokens' : 'max_tokens';
    }

    private function host(): string
    {
        return strtolower((string) parse_url((string) ($this->config['url'] ?? ''), PHP_URL_HOST));
    }

    private function ensureReady(): void
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }
    }

    private function request(int $timeout): PendingRequest
    {
        return Http::baseUrl(rtrim((string) $this->config['url'], '/'))
            ->withToken((string) $this->config['key'])
            ->acceptJson()
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

    private function timeout(): int
    {
        return (int) ($this->config['timeout'] ?? 180);
    }

    private function isTransient(Throwable $e): bool
    {
        return $e instanceof ConnectionException
            || ($e instanceof RequestException && ($e->response->serverError() || $e->response->status() === 429));
    }

    private function failureMessage(Response $response): string
    {
        if (\in_array($response->status(), [401, 403], true)) {
            return 'The AI service rejected the API key. Check OPENAI_API_KEY in the .env file.';
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
