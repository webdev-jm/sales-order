<?php

namespace App\Services\Ai\Concerns;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\Agents\JsonDocumentAgent;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Laravel\Ai\Exceptions\FailoverableException;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Files\File;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Responses\AgentResponse;
use Throwable;

/**
 * generateJson() for providers that laravel/ai calls. The provider supplies failureMessage()
 * for an HTTP error reply, retryCount() and retryDelayMs() for retries after an overload.
 */
trait GeneratesJsonWithLaravelAi
{
    use DescribesLaravelAiFailures;

    /** Picture types the vision models read. */
    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens): AiResponse
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        $agent = new JsonDocumentAgent($instructions, $schema, $maxOutputTokens, $this->timeoutSeconds(), $this->laravelAiOptions());

        $attachments = array_map(fn (AiDocument $document): File => $this->attachmentFor($document), $documents);

        $startedAt = hrtime(true);

        try {
            $response = retry(
                1 + $this->retryCount(),
                fn () => $this->promptOnce($agent, $prompt, $attachments),
                $this->retryDelayMs(),
                fn (Throwable $e): bool => $this->isTransient($e),
            );
        } catch (FailoverableException|RequestException $e) {
            throw $this->failureFor($e);
        }

        return AiResponse::fromAgentResponse($response, $this->name(), $this->model(), (int) ((hrtime(true) - $startedAt) / 1_000_000));
    }

    /**
     * Turns any laravel/ai failure into a message that can be shown to the user; overloads,
     * rate limits, server errors and dropped connections are left as they are so they can be retried.
     *
     * @param array<int, File> $attachments
     */
    private function promptOnce(JsonDocumentAgent $agent, string $prompt, array $attachments): AgentResponse
    {
        try {
            return $agent->prompt(
                $prompt,
                attachments: $attachments,
                provider: app(AiClient::class)->laravelAiProviders([$this]),
                timeout: $this->timeoutSeconds(),
            );
        } catch (Throwable $e) {
            throw $this->isTransient($e) ? $e : $this->failureFor($e);
        }
    }

    /**
     * PDFs go as documents and pictures as images; anything else is refused before calling out.
     */
    private function attachmentFor(AiDocument $document): File
    {
        if ($document->mimeType === 'application/pdf') {
            return Document::fromString($document->contents, $document->mimeType);
        }

        if (\in_array($document->mimeType, self::IMAGE_TYPES, true)) {
            return Image::fromBase64(base64_encode($document->contents), $document->mimeType);
        }

        throw new AiUnavailableException($this->label() . ' cannot read ' . $document->mimeType . ' files.');
    }

    /**
     * Providers are often briefly overloaded; one retry after a short pause usually gets through.
     */
    private function isTransient(Throwable $e): bool
    {
        return $e instanceof FailoverableException
            || ($e instanceof RequestException && $e->response instanceof Response && $e->response->serverError());
    }
}
