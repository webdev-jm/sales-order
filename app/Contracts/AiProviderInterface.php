<?php

namespace App\Contracts;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;
use Throwable;

/**
 * One AI vendor (Gemini, Claude, ...). The application talks to App\Services\Ai\AiClient, which
 * picks the configured provider and fails over to the next one; it never calls a provider directly.
 */
interface AiProviderInterface
{
    /**
     * Short name kept in the usage log and used in AI_PROVIDER, e.g. "gemini".
     */
    public function name(): string;

    /**
     * Name shown to users, e.g. "Gemini".
     */
    public function label(): string;

    public function model(): string;

    /**
     * The effort levels this provider accepts, lowest first. A blank effort leaves the model's default.
     *
     * @return array<int, string>
     */
    public function effortLevels(): array;

    /**
     * Why this provider cannot be called yet, or null when it is ready.
     */
    public function setupProblem(): ?string;

    /**
     * Longest one call can take, retries included, in seconds.
     */
    public function maxDurationSeconds(): int;

    /**
     * Checks the key and the model without using tokens.
     *
     * @return string What was confirmed, to show to the user
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function checkConnection(): string;

    /**
     * The model IDs the key can use, sorted. Costs no tokens.
     *
     * @return array<int, string>
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function listModels(): array;

    /**
     * The laravel/ai provider configuration (driver, key, url) that reaches this provider with
     * the model and server saved on the AI Settings page.
     *
     * @return array{driver: string, key: string|null, url?: string|null}
     */
    public function laravelAiConfig(): array;

    /**
     * Request options laravel/ai merges into this provider's request body, such as the effort
     * level. Empty when the model's defaults apply.
     *
     * @return array<string, mixed>
     */
    public function laravelAiOptions(): array;

    /**
     * Seconds one request may take before it is abandoned.
     */
    public function timeoutSeconds(): int;

    /**
     * The message to show for a failed laravel/ai call to this provider.
     */
    public function failureFor(Throwable $e): AiUnavailableException;

    /**
     * Asks for a JSON reply that follows the given schema.
     *
     * @param array<int, AiDocument> $documents Files the model reads, such as a PDF
     * @param array<string, mixed> $schema JSON Schema the reply must follow
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens): AiResponse;
}
