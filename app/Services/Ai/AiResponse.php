<?php

namespace App\Services\Ai;

use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\FinishReason;
use Laravel\Ai\Responses\StructuredAgentResponse;

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
     * A laravel/ai agent reply in the application's shape. laravel/ai already counts cache reads
     * and writes as input and thinking tokens as output.
     */
    public static function fromAgentResponse(AgentResponse $response, string $provider, string $fallbackModel, int $durationMs): self
    {
        $usage = $response->usage;
        $finishReason = $response->steps->last()?->finishReason;

        $text = $response instanceof StructuredAgentResponse && $response->structured !== []
            ? (string) json_encode($response->structured)
            : (string) $response->text;

        return new self(
            text: $text,
            provider: $provider,
            model: (string) ($response->meta->model ?: $fallbackModel),
            inputTokens: $usage->inputTokens,
            outputTokens: $usage->outputTokens,
            totalTokens: $usage->inputTokens + $usage->outputTokens,
            durationMs: $durationMs,
            cutOff: $finishReason === FinishReason::Length,
            refused: $finishReason === FinishReason::ContentFilter,
        );
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
