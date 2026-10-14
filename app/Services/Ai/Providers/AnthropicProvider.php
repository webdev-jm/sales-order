<?php

namespace App\Services\Ai\Providers;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\InternalServerException;
use Anthropic\Core\Exceptions\NotFoundException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\Messages\Base64ImageSource;
use Anthropic\Messages\Base64PDFSource;
use Anthropic\Messages\DocumentBlockParam;
use Anthropic\Messages\ImageBlockParam;
use Anthropic\Messages\Message;
use Anthropic\Messages\TextBlockParam;
use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Str;
use Psr\Http\Client\ClientInterface;

/**
 * Anthropic Claude through the official Anthropic PHP SDK (Messages API, structured outputs).
 */
class AnthropicProvider implements AiProviderInterface
{
    public const NAME = 'anthropic';

    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /** The SDK backs off at most this long between retries. */
    private const MAX_RETRY_DELAY_SECONDS = 8;

    /**
     * @param array{key?: string|null, model?: string, timeout?: int, effort?: string|null, max_retries?: int, retry_ms?: int} $config
     * @param ClientInterface|null $transporter HTTP client for the SDK; tests pass a mocked one
     */
    public function __construct(private array $config, private ?ClientInterface $transporter = null)
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

    public function setupProblem(): ?string
    {
        return blank($this->config['key'] ?? null)
            ? 'AI is not set up yet. Add ANTHROPIC_API_KEY to the .env file.'
            : null;
    }

    public function maxDurationSeconds(): int
    {
        $retries = $this->maxRetries();

        return ($retries + 1) * $this->timeout() + $retries * self::MAX_RETRY_DELAY_SECONDS;
    }

    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens): AiResponse
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        $content = array_map(fn (AiDocument $document): DocumentBlockParam|ImageBlockParam => $this->blockFor($document), $documents);
        $content[] = TextBlockParam::with(text: $prompt);

        $outputConfig = ['format' => ['type' => 'json_schema', 'schema' => self::strictSchema($schema)]];

        if (filled($effort = $this->config['effort'] ?? null)) {
            $outputConfig['effort'] = $effort;
        }

        $startedAt = hrtime(true);

        try {
            $message = $this->client()->messages->create(
                maxTokens: $maxOutputTokens,
                messages: [['role' => 'user', 'content' => $content]],
                model: $this->model(),
                system: $instructions,
                outputConfig: $outputConfig,
            );
        } catch (RateLimitException) {
            throw new AiUnavailableException('The AI service is busy or its quota is used up. Try again later.');
        } catch (InternalServerException) {
            throw new AiUnavailableException('The AI service is having problems. Try again in a minute.');
        } catch (AuthenticationException|PermissionDeniedException) {
            throw new AiUnavailableException('The AI service rejected the API key. Check ANTHROPIC_API_KEY in the .env file.');
        } catch (NotFoundException) {
            throw new AiUnavailableException('The AI model "' . $this->model() . '" was not found. Check ANTHROPIC_MODEL in the .env file.');
        } catch (APIStatusException $e) {
            throw new AiUnavailableException('The AI service refused the request: '
                . Str::limit((string) data_get($e->body, 'error.message', $e->getMessage()), 200));
        } catch (APIConnectionException $e) {
            throw new AiUnavailableException('The AI service could not be reached. Try again in a minute.', 0, $e);
        } catch (AnthropicException $e) {
            throw new AiUnavailableException('The AI service failed: ' . Str::limit($e->getMessage(), 200), 0, $e);
        }

        return $this->responseFrom($message, (int) ((hrtime(true) - $startedAt) / 1_000_000));
    }

    /**
     * Claude's structured outputs need every object closed with additionalProperties false, and
     * take a nullable type as anyOf rather than a list of types.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public static function strictSchema(array $schema): array
    {
        if (isset($schema['type']) && \is_array($schema['type'])) {
            $rest = $schema;
            unset($rest['type']);

            return ['anyOf' => array_map(
                fn (string $type): array => self::strictSchema($type === 'null' ? ['type' => 'null'] : ['type' => $type] + $rest),
                $schema['type']
            )];
        }

        if (($schema['type'] ?? null) === 'object') {
            $schema['additionalProperties'] = false;
            $schema['properties'] = array_map(fn (array $property): array => self::strictSchema($property), $schema['properties'] ?? []);
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

    private function client(): Client
    {
        return new Client(
            apiKey: (string) $this->config['key'],
            requestOptions: [
                'transporter'       => $this->transporter ?? new GuzzleClient(['timeout' => $this->timeout(), 'connect_timeout' => 15]),
                'timeout'           => (float) $this->timeout(),
                'maxRetries'        => $this->maxRetries(),
                'initialRetryDelay' => ($this->config['retry_ms'] ?? 500) / 1000,
            ],
        );
    }

    private function blockFor(AiDocument $document): DocumentBlockParam|ImageBlockParam
    {
        $data = base64_encode($document->contents);

        if ($document->mimeType === 'application/pdf') {
            return DocumentBlockParam::with(source: Base64PDFSource::with(data: $data));
        }

        if (\in_array($document->mimeType, self::IMAGE_TYPES, true)) {
            return ImageBlockParam::with(source: Base64ImageSource::with(data: $data, mediaType: $document->mimeType));
        }

        throw new AiUnavailableException('Claude cannot read ' . $document->mimeType . ' files.');
    }

    /**
     * Cache reads and writes are input too, so they count towards the input tokens.
     */
    private function responseFrom(Message $message, int $durationMs): AiResponse
    {
        $text = collect($message->content)
            ->filter(fn (object $block): bool => $block->type === 'text')
            ->pluck('text')
            ->implode('');

        $input = $message->usage->inputTokens
            + (int) $message->usage->cacheCreationInputTokens
            + (int) $message->usage->cacheReadInputTokens;
        $output = $message->usage->outputTokens;

        return new AiResponse(
            text: $text,
            provider: self::NAME,
            model: $message->model,
            inputTokens: $input,
            outputTokens: $output,
            totalTokens: $input + $output,
            durationMs: $durationMs,
            cutOff: $message->stopReason === 'max_tokens',
            refused: $message->stopReason === 'refusal',
        );
    }

    private function timeout(): int
    {
        return (int) ($this->config['timeout'] ?? 180);
    }

    private function maxRetries(): int
    {
        return (int) ($this->config['max_retries'] ?? 1);
    }
}
