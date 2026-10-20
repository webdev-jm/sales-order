<?php

namespace Tests\Concerns;

/**
 * Builds Gemini Interactions API replies (the endpoint laravel/ai calls) for Http::fake().
 */
trait FakesGemini
{
    /**
     * Gemini as the only provider. The failover list and the other keys are cleared so no test
     * reaches a real AI API through keys in the developer's .env file. Saved scans are off, so a
     * test that scans the same bytes twice reaches the fake both times; cache tests turn them on.
     */
    protected function configureGemini(): void
    {
        config([
            'ai.enabled'                => true,
            'ai.provider'               => 'gemini',
            'ai.failover'               => '',
            'ai.providers.anthropic.key' => null,
            'ai.providers.openai.key'   => null,
            'ai.providers.gemini.key'   => 'test-key',
            'ai.providers.gemini.url'   => 'https://generativelanguage.googleapis.com/v1beta/',
            'ai.providers.gemini.model' => 'gemini-test',
            'ai.providers.gemini.effort' => 'low',
            'ai.providers.gemini.retry_ms' => 0,
            'ai.po_scan.cache_days'     => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $json The structured answer the model returns
     * @param string $finishReason STOP, MAX_TOKENS (cut off) or SAFETY (withheld)
     * @return array<string, mixed>
     */
    protected function geminiReply(array $json, string $finishReason = 'STOP', int $input = 1200, int $output = 300, int $thoughts = 50): array
    {
        $reply = [
            'status' => match ($finishReason) {
                'MAX_TOKENS' => 'incomplete',
                'SAFETY'     => 'failed',
                default      => 'completed',
            },
            'steps' => [
                ['type' => 'thought', 'summary' => [['type' => 'text', 'text' => 'Reading the order.']]],
                ['type' => 'model_output', 'content' => [['type' => 'text', 'text' => json_encode($json)]]],
            ],
            'usage' => [
                'total_input_tokens'   => $input,
                'total_output_tokens'  => $output,
                'total_thought_tokens' => $thoughts,
            ],
        ];

        if ($finishReason === 'SAFETY') {
            $reply['errors'] = [['code' => 'SAFETY', 'message' => 'The response was blocked for safety reasons.']];
        }

        return $reply;
    }
}
