<?php

namespace App\Contracts;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\AiResponse;

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
     * Why this provider cannot be called yet, or null when it is ready.
     */
    public function setupProblem(): ?string;

    /**
     * Longest one call can take, retries included, in seconds.
     */
    public function maxDurationSeconds(): int;

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
