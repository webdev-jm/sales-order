<?php

namespace App\Services\Ai\Concerns;

use App\Services\Ai\AiClient;
use Laravel\Ai\Enums\Lab;

/**
 * For agents run by App\Services\Ai\AgentRunner: each provider gets the effort level saved for it
 * on the AI Settings page. laravel/ai passes either the provider's own name or its driver here.
 */
trait UsesAiSettings
{
    public function providerOptions(Lab|string $provider): array
    {
        $name = $provider instanceof Lab ? $provider->value : $provider;

        return app(AiClient::class)->providerForLaravelAi($name)?->laravelAiOptions() ?? [];
    }
}
