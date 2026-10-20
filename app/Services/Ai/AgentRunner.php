<?php

namespace App\Services\Ai;

use App\Exceptions\AiUnavailableException;
use App\Models\AiUsageLog;
use App\Models\User;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Responses\AgentResponse;
use Throwable;

/**
 * Runs a laravel/ai agent the way the PO scanner runs its calls: AI must be on and within the
 * token limits, each ready provider is tried in the order of the AI Settings page, and every
 * attempt is logged on the AI Usage page with its tokens and cost.
 */
class AgentRunner
{
    public function __construct(private AiClient $client, private AiUsageMeter $meter)
    {
    }

    /**
     * Why the agent cannot be run for this user now, or null when it can.
     */
    public function problemFor(?User $user): ?string
    {
        return $this->client->setupProblem() ?? $this->meter->budgetProblem($user);
    }

    /**
     * @throws AiUnavailableException with a message that can be shown to the user
     */
    public function prompt(Agent $agent, string $prompt, string $feature, ?User $user, ?int $accountId = null, ?string $reference = null): AgentResponse
    {
        if (($problem = $this->problemFor($user)) !== null) {
            throw new AiUnavailableException($problem);
        }

        $log = [
            'user_id'    => $user?->id,
            'account_id' => $accountId,
            'feature'    => $feature,
            'reference'  => $reference,
        ];

        $failures = [];

        foreach ($this->client->availableProviders() as $provider) {
            $startedAt = hrtime(true);

            try {
                $response = $agent->prompt(
                    $prompt,
                    provider: $this->client->laravelAiProviders([$provider]),
                    timeout: $provider->timeoutSeconds(),
                );
            } catch (Throwable $e) {
                $error = $provider->failureFor($e);
                $failures[$provider->label()] = $error;

                $this->meter->record($log + ['status' => AiUsageLog::STATUS_FAILED, 'error' => $error->getMessage()], $provider);

                continue;
            }

            $this->meter->record(
                $log + ['status' => AiUsageLog::STATUS_SUCCESS],
                AiResponse::fromAgentResponse($response, $provider->name(), $provider->model(), (int) ((hrtime(true) - $startedAt) / 1_000_000))
            );

            return $response;
        }

        if (\count($failures) === 1) {
            throw reset($failures);
        }

        throw new AiUnavailableException('No AI provider could answer. ' . implode(' ', array_map(
            fn (string $label, AiUnavailableException $e): string => "{$label}: {$e->getMessage()}",
            array_keys($failures),
            $failures
        )));
    }
}
