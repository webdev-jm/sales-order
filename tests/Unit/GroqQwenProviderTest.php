<?php

namespace Tests\Unit;

use App\Exceptions\AiUnavailableException;
use App\Services\Ai\AiDocument;
use App\Services\Ai\Providers\GroqProvider;
use App\Services\Ai\Providers\QwenProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqQwenProviderTest extends TestCase
{
    private function groq(array $overrides = []): GroqProvider
    {
        return new GroqProvider(array_merge([
            'key'      => 'test-groq-key',
            'url'      => 'https://api.groq.com/openai/v1/',
            'model'    => 'openai/gpt-oss-120b',
            'timeout'  => 30,
            'effort'   => 'low',
            'retry_ms' => 0,
        ], $overrides));
    }

    private function qwen(array $overrides = []): QwenProvider
    {
        return new QwenProvider(array_merge([
            'key'      => 'test-qwen-key',
            'url'      => 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/',
            'model'    => 'qwen-plus',
            'timeout'  => 30,
            'effort'   => null,
            'retry_ms' => 0,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function reply(string $model): array
    {
        return [
            'model'   => $model,
            'choices' => [['message' => ['role' => 'assistant', 'content' => '{"ok":true}'], 'finish_reason' => 'stop']],
            'usage'   => [
                'prompt_tokens'         => 120,
                'completion_tokens'     => 30,
                'total_tokens'          => 150,
                'prompt_tokens_details' => ['cached_tokens' => 100],
            ],
        ];
    }

    public function test_they_name_themselves_and_their_keys(): void
    {
        $this->assertSame(['groq', 'Groq'], [$this->groq()->name(), $this->groq()->label()]);
        $this->assertSame(['qwen', 'Qwen'], [$this->qwen()->name(), $this->qwen()->label()]);
        $this->assertSame(['low', 'medium', 'high'], $this->groq()->effortLevels());
        $this->assertSame([], $this->qwen()->effortLevels());
        $this->assertNull($this->groq()->setupProblem());
        $this->assertSame('AI is not set up yet. Add GROQ_API_KEY to the .env file.', $this->groq(['key' => null])->setupProblem());
        $this->assertSame('AI is not set up yet. Set the server address for Qwen on the AI Settings page.', $this->qwen(['url' => ''])->setupProblem());
    }

    public function test_they_read_pictures_but_not_pdfs(): void
    {
        foreach ([$this->groq(), $this->qwen()] as $provider) {
            $this->assertFalse($provider->canRead('application/pdf'), $provider->label());
            $this->assertTrue($provider->canRead('image/png'), $provider->label());
        }
    }

    public function test_a_pdf_is_refused_before_anything_is_sent(): void
    {
        Http::fake();

        try {
            $this->groq()->generateJson('Be exact.', 'Read this.', [AiDocument::pdf('%PDF-1.4', 'po.pdf')], ['type' => 'object', 'properties' => []], 1000);
            $this->fail('No exception was thrown.');
        } catch (AiUnavailableException $e) {
            $this->assertSame('Groq cannot read application/pdf files.', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_groq_answers_json_and_logs_itself_as_groq(): void
    {
        Http::fake(['api.groq.com/*' => Http::response($this->reply('openai/gpt-oss-120b'))]);

        $response = $this->groq()->generateJson('Be exact.', 'Sum it up.', [new AiDocument('image/png', 'png-bytes')], ['type' => 'object', 'properties' => ['ok' => ['type' => 'boolean']]], 1000);

        $this->assertSame('groq', $response->provider);
        $this->assertSame(['ok' => true], $response->json());
        $this->assertSame(100, $response->cachedInputTokens);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && $request->data()['max_tokens'] === 1000
            && $request->data()['reasoning_effort'] === 'low'
            && $request->data()['messages'][1]['content'][0]['type'] === 'image_url');
    }

    public function test_qwen_sends_no_effort_and_reaches_the_region_it_is_set_to(): void
    {
        Http::fake(['dashscope.aliyuncs.com/*' => Http::response($this->reply('qwen-plus'))]);

        $response = $this->qwen(['url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/'])
            ->generateJson('Be exact.', 'Sum it up.', [], ['type' => 'object', 'properties' => []], 500);

        $this->assertSame('qwen', $response->provider);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions'
            && $request->header('Authorization') === ['Bearer test-qwen-key']
            && !array_key_exists('reasoning_effort', $request->data()));
    }

    public function test_a_rejected_key_names_the_providers_env_setting(): void
    {
        Http::fake(['dashscope-intl.aliyuncs.com/*' => Http::response(['error' => ['message' => 'Incorrect API key']], 401)]);

        try {
            $this->qwen()->listModels();
            $this->fail('No exception was thrown.');
        } catch (AiUnavailableException $e) {
            $this->assertSame('The AI service rejected the API key. Check QWEN_API_KEY in the .env file.', $e->getMessage());
        }
    }

    public function test_agents_reach_groq_through_laravel_ais_groq_driver(): void
    {
        $this->assertSame(['driver' => 'groq', 'key' => 'test-groq-key', 'url' => 'https://api.groq.com/openai/v1/'], $this->groq()->laravelAiConfig());
        $this->assertSame(['driver' => 'openai-compatible', 'key' => 'test-qwen-key', 'url' => 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/'], $this->qwen()->laravelAiConfig());
    }
}
