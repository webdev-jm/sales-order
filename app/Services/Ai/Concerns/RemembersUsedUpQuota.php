<?php

namespace App\Services\Ai\Concerns;

use App\Services\Ai\AiQuota;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;

/**
 * For providers that can tell a used-up quota (a daily limit, no credits) apart from a short rate limit. The
 * provider supplies usedUpQuota() for an error reply; a used-up quota is never retried, and the provider is
 * left out of the failover chain until the quota resets (AiQuota).
 */
trait RemembersUsedUpQuota
{
    /**
     * When the error reply says the quota or credits are used up: until when, and a message for the user.
     *
     * When "model" is given, only that model's quota is used up; otherwise the whole provider's is.
     *
     * @return array{until: CarbonInterface, reason: string, model?: string}|null
     */
    abstract protected function usedUpQuota(Response $response): ?array;

    /**
     * The message for a used-up quota, after remembering it, or null when the reply is about something else.
     */
    protected function quotaFailure(Response $response): ?string
    {
        if (($usedUp = $this->usedUpQuota($response)) === null) {
            return null;
        }

        AiQuota::block($this->name(), $usedUp['until'], $usedUp['reason'], $usedUp['model'] ?? null);

        return $usedUp['reason'];
    }

    /**
     * Whether the failure, or the HTTP error behind it, is a used-up quota.
     */
    protected function isUsedUpQuota(Throwable $e): bool
    {
        for ($cause = $e; $cause !== null; $cause = $cause->getPrevious()) {
            if ($cause instanceof RequestException && $cause->response instanceof Response) {
                return $this->usedUpQuota($cause->response) !== null;
            }
        }

        return false;
    }
}
