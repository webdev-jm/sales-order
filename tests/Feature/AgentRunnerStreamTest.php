<?php

namespace Tests\Feature;

use App\Exceptions\AiUnavailableException;
use App\Models\AiUsageLog;
use App\Services\Ai\AgentRunner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Streaming\Events\TextDelta;
use RuntimeException;
use Tests\Concerns\FakesAnthropic;
use Tests\Concerns\FakesGemini;
use Tests\TestCase;

class AgentRunnerStreamTest extends TestCase
{
    use DatabaseTransactions;
    use FakesAnthropic;
    use FakesGemini;

    private const FEATURE = 'stream_test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureGemini();
        $this->configureAnthropic();
        config(['ai.provider' => 'gemini', 'ai.failover' => 'claude']);
    }

    /**
     * An agent whose first stream fails in the given way; later streams answer normally (faked).
     *
     * @param 'before'|'midway' $failure
     */
    private function flakyAgent(string $failure): Agent
    {
        $agent = new class implements Agent
        {
            use Promptable {
                stream as promptableStream;
            }

            public static string $failure = 'before';

            public static int $calls = 0;

            public function instructions(): string
            {
                return 'Answer briefly.';
            }

            public function stream($prompt, array $attachments = [], $provider = null, ?string $model = null, ?int $timeout = null): StreamableAgentResponse
            {
                if (++static::$calls === 1) {
                    if (static::$failure === 'before') {
                        throw new RuntimeException('Provider down.');
                    }

                    return new StreamableAgentResponse('failing', function () {
                        yield new TextDelta('1', 'm1', 'Half an ans', time());

                        throw new RuntimeException('Connection dropped.');
                    });
                }

                return $this->promptableStream($prompt, $attachments, $provider, $model, $timeout);
            }
        };

        $agent::$failure = $failure;
        $agent::$calls = 0;
        $agent::fake(['Full answer.']);

        return $agent;
    }

    /**
     * @return array<int, string>
     */
    private function statuses(): array
    {
        return AiUsageLog::query()->where('feature', self::FEATURE)->orderBy('id')->pluck('status')->all();
    }

    public function test_a_provider_that_fails_before_writing_anything_is_failed_over(): void
    {
        $text = '';

        $response = app(AgentRunner::class)->stream($this->flakyAgent('before'), 'Hello?', self::FEATURE, null, function ($event) use (&$text): void {
            if ($event instanceof TextDelta) {
                $text .= $event->delta;
            }
        });

        $this->assertSame('Full answer.', $text);
        $this->assertSame('Full answer.', $response->text);
        $this->assertSame([AiUsageLog::STATUS_FAILED, AiUsageLog::STATUS_SUCCESS], $this->statuses());
    }

    public function test_a_provider_that_fails_part_way_through_an_answer_is_not_failed_over(): void
    {
        $text = '';

        try {
            app(AgentRunner::class)->stream($this->flakyAgent('midway'), 'Hello?', self::FEATURE, null, function ($event) use (&$text): void {
                if ($event instanceof TextDelta) {
                    $text .= $event->delta;
                }
            });
            $this->fail('Expected an AiUnavailableException.');
        } catch (AiUnavailableException $e) {
            $this->assertStringContainsString('Connection dropped.', $e->getMessage());
        }

        $this->assertSame('Half an ans', $text);
        $this->assertSame([AiUsageLog::STATUS_FAILED], $this->statuses());
    }
}
