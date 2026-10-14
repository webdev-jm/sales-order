<?php

namespace Tests\Concerns;

/**
 * Builds Gemini generateContent replies for Http::fake().
 */
trait FakesGemini
{
    /**
     * Gemini as the only provider. The failover list and the Anthropic key are cleared so no test
     * reaches the real Claude API through keys in the developer's .env file.
     */
    protected function configureGemini(): void
    {
        config([
            'ai.enabled'                => true,
            'ai.provider'               => 'gemini',
            'ai.failover'               => '',
            'ai.providers.anthropic.key' => null,
            'ai.providers.gemini.key'   => 'test-key',
            'ai.providers.gemini.model' => 'gemini-test',
            'ai.providers.gemini.thinking_level' => 'low',
            'ai.providers.gemini.retry_ms' => 0,
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
