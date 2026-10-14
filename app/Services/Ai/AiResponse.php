<?php

namespace App\Services\Ai;

/**
 * The text of an AI reply, which provider and model wrote it, and the tokens it used. Every
 * provider fills this the same way, so callers never see a vendor's reply format.
 */
class AiResponse
{
    public function __construct(
        public readonly string $text,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly int $totalTokens,
        public readonly int $durationMs,
        public readonly bool $cutOff = false,
        public readonly bool $refused = false,
    ) {
    }

    /**
     * The reply decoded as JSON, or null when it is not a JSON object.
     *
     * @return array<string, mixed>|null
     */
    public function json(): ?array
    {
        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($this->text)));
        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * The model stopped at the output token cap, so the JSON is incomplete.
     */
    public function wasCutOff(): bool
    {
        return $this->cutOff;
    }

    /**
     * The provider's safety checks declined to answer.
     */
    public function wasRefused(): bool
    {
        return $this->refused;
    }
}
