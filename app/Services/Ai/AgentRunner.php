<?php

namespace App\Services\Ai;

use App\Exceptions\AiUnavailableException;
use App\Models\AiUsageLog;
use App\Models\User;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\StreamedAgentResponse;
use Laravel\Ai\Streaming\Events\StreamEvent;
use Throwable;

/**
 * Runs a laravel/ai agent the way the PO scanner runs its calls: AI must be on and within the
 * token limits, each ready provider is tried in the order set for the function ($feature) on the
 * AI Settings page, and every attempt is logged on the AI Usage page with its tokens and cost.
 */
class AgentRunner
{
    public function __construct(private AiClient $client, private AiUsageMeter $meter)
    {
    }

    /**
     * Why the agent cannot be run for this user now, or null when it can.
     *
     * @param string|null $feature the AI function (AiUsageLog::FEATURE_*), whose own providers are checked when it has any
     */
    public function problemFor(?User $user, ?string $feature = null): ?string
    {
        return $this->client->forFeature($feature)->setupProblem() ?? $this->meter->budgetProblem($user);
    }

    /**
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function prompt(Agent $agent, string $prompt, string $feature, ?User $user, ?int $accountId = null, ?string $reference = null): AgentResponse
    {
        if (($problem = $this->problemFor($user, $feature)) !== null) {
            throw new AiUnavailableException($problem);
        }

        $client = $this->client->forFeature($feature);

        $log = [
            'user_id'    => $user?->id,
            'account_id' => $accountId,
            'feature'    => $feature,
            'reference'  => $reference,
        ];

        $failures = [];

        foreach ($client->availableProviders() as $provider) {
            $startedAt = hrtime(true);

            try {
                $response = $agent->prompt(
                    $prompt,
                    provider: $client->laravelAiProviders([$provider]),
                    timeout: $provider->timeoutSeconds(),
                );
            } catch (Throwable $e) {
                $error = $provider->failureFor($e);
                $failures[AiClient::failureKey($failures, $provider)] = $error;

                $this->meter->record($log + ['status' => AiUsageLog::STATUS_FAILED, 'error' => $error->getMessage()], $provider);

                continue;
            }

            $this->meter->record(
                $log + ['status' => AiUsageLog::STATUS_SUCCESS],
                AiResponse::fromAgentResponse($response, $provider->name(), $provider->model(), (int) ((hrtime(true) - $startedAt) / 1_000_000))
            );

            return $response;
        }

        throw $this->noProviderAnswered($failures);
    }

    /**
     * Like prompt(), but hands each event (text as it is written, tool calls) to $onEvent as it arrives. A
     * provider that fails before sending anything is failed over; one that fails part-way through an answer
     * is not, because the user has already seen part of it.
     *
     * @param callable(StreamEvent): void $onEvent
     *
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function stream(Agent $agent, string $prompt, string $feature, ?User $user, callable $onEvent, ?int $accountId = null, ?string $reference = null): AgentResponse
    {
        if (($problem = $this->problemFor($user, $feature)) !== null) {
            throw new AiUnavailableException($problem);
        }

        $client = $this->client->forFeature($feature);

        $log = [
            'user_id'    => $user?->id,
            'account_id' => $accountId,
            'feature'    => $feature,
            'reference'  => $reference,
        ];

        $failures = [];

        foreach ($client->availableProviders() as $provider) {
            $startedAt = hrtime(true);
            $finished = null;
            $sentSomething = false;

            try {
                $stream = $agent->stream(
                    $prompt,
                    provider: $client->laravelAiProviders([$provider]),
                    timeout: $provider->timeoutSeconds(),
                )->then(function (StreamedAgentResponse $response) use (&$finished): void {
                    $finished = $response;
                });

                foreach ($stream as $event) {
                    $sentSomething = true;
                    $onEvent($event);
                }
            } catch (Throwable $e) {
                $error = $provider->failureFor($e);
                $failures[AiClient::failureKey($failures, $provider)] = $error;

                $this->meter->record($log + ['status' => AiUsageLog::STATUS_FAILED, 'error' => $error->getMessage()], $provider);

                if ($sentSomething) {
                    throw $error;
                }

                continue;
            }

            $this->meter->record(
                $log + ['status' => AiUsageLog::STATUS_SUCCESS],
                AiResponse::fromAgentResponse($finished, $provider->name(), $provider->model(), (int) ((hrtime(true) - $startedAt) / 1_000_000))
            );

            return $finished;
        }

        throw $this->noProviderAnswered($failures);
    }

    /**
     * @param array<string, AiUnavailableException> $failures provider label => why it failed
     */
    private function noProviderAnswered(array $failures): AiUnavailableException
    {
        if (\count($failures) === 1) {
            return reset($failures);
        }

        return new AiUnavailableException('No AI provider could answer. ' . implode(' ', array_map(
            fn (string $label, AiUnavailableException $e): string => "{$label}: {$e->getMessage()}",
            array_keys($failures),
            $failures
        )));
    }
}
