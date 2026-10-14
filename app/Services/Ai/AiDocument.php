<?php

namespace App\Services\Ai;

/**
 * A file sent to the model with the prompt.
 */
class AiDocument
{
    public function __construct(
        public readonly string $mimeType,
        public readonly string $contents,
        public readonly string $name = '',
    ) {
    }

    public static function pdf(string $contents, string $name = ''): self
    {
        return new self('application/pdf', $contents, $name);
    }
}
