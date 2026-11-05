<?php

namespace App\Services\Ai\Providers;

/**
 * Groq, which runs open models (gpt-oss, Qwen, Llama) on its own fast hardware, through its
 * OpenAI-compatible API. Agents go through laravel/ai's Groq driver. Groq reads text and, on
 * vision models, images, but no PDF files, so PO scans go to the next provider that reads them.
 */
class GroqProvider extends OpenAiProvider
{
    public const NAME = 'groq';

    public function name(): string
    {
        return self::NAME;
    }

    public function label(): string
    {
        return 'Groq';
    }

    /**
     * reasoning_effort as the gpt-oss models take it.
     */
    public function effortLevels(): array
    {
        return ['low', 'medium', 'high'];
    }

    public function canRead(string $mimeType): bool
    {
        return \in_array($mimeType, self::IMAGE_TYPES, true);
    }

    public function laravelAiConfig(): array
    {
        return ['driver' => 'groq'] + parent::laravelAiConfig();
    }

    protected function keyName(): string
    {
        return 'GROQ_API_KEY';
    }

    protected function missingKeyHint(): string
    {
        return 'Add ' . $this->keyName() . ' to the .env file.';
    }
}
