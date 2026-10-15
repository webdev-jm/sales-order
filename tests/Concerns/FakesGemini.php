<?php

namespace Tests\Concerns;

/**
 * Builds Gemini generateContent replies for Http::fake().
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
            'ai.providers.gemini.model' => 'gemini-test',
            'ai.providers.gemini.effort' => 'low',
            'ai.providers.gemini.retry_ms' => 0,
            'ai.po_scan.cache_days'     => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $json The structured answer the model returns
     * @return array<string, mixed>
     */
    protected function geminiReply(array $json, string $finishReason = 'STOP', int $input = 1200, int $output = 300, int $thoughts = 50): array
    {
        return [
            'candidates' => [[
                'content'      => ['role' => 'model', 'parts' => [['text' => json_encode($json)]]],
                'finishReason' => $finishReason,
            ]],
            'usageMetadata' => [
                'promptTokenCount'     => $input,
                'candidatesTokenCount' => $output,
                'thoughtsTokenCount'   => $thoughts,
                'totalTokenCount'      => $input + $output + $thoughts,
            ],
            'modelVersion' => 'gemini-test-001',
        ];
    }
}
