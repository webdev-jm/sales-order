<?php

namespace App\Services\Ai\Providers;

/**
 * Qwen on Alibaba Cloud Model Studio (DashScope), through its OpenAI-compatible mode. The server
 * address depends on the region the key belongs to, so it can be changed on the AI Settings page.
 * Qwen reads text and, on its VL models, images, but no PDF files, so PO scans go to the next
 * provider that reads them.
 */
class QwenProvider extends OpenAiProvider
{
    public const NAME = 'qwen';

    public function name(): string
    {
        return self::NAME;
    }

    public function label(): string
    {
        return 'Qwen';
    }

    /**
     * Qwen turns thinking on and off per model rather than taking an effort level.
     */
    public function effortLevels(): array
    {
        return [];
    }

    public function canRead(string $mimeType): bool
    {
        return \in_array($mimeType, self::IMAGE_TYPES, true);
    }

    protected function keyName(): string
    {
        return 'QWEN_API_KEY';
    }

    protected function missingKeyHint(): string
    {
        return 'Add ' . $this->keyName() . ' to the .env file.';
    }
}
