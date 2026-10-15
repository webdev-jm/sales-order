<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use Closure;

/**
 * The AI entry point for the application. It sends a request to the first provider in the
 * failover order and, when that provider cannot answer (unreachable, overloaded, out of quota,
 * refusing the request or missing its key), fails over to the next ones. The order is the one
 * saved on the AI Settings page, or AI_PROVIDER then AI_FAILOVER until that page is saved.
 * A provider without an API key is skipped.
 */
class AiClient
{
    public function __construct(private AiManager $manager)
    {
    }

    /**
     * Provider names in the order they are tried, as configured (unknown names included).
     *
     * @return array<int, string>
     */
    public function configuredNames(): array
    {
        if ($this->manager->isManagedInApp()) {
            return $this->manager->savedSettings()->where('is_enabled', true)->keys()->values()->all();
        }

        $names = [(string) config('ai.provider'), ...explode(',', (string) config('ai.failover'))];

        $names = array_map(fn (string $name): string => $this->manager->resolveName($name) ?? trim($name), $names);

        return array_values(array_unique(array_filter($names, fn (string $name): bool => $name !== '')));
    }

    /**
     * The known providers in the order they are tried, ready or not.
     *
     * @return array<int, AiProviderInterface>
     */
    public function chain(): array
    {
        return array_values(array_map(
            fn (string $name): AiProviderInterface => $this->manager->provider($name),
            array_filter($this->configuredNames(), fn (string $name): bool => $this->manager->resolveName($name) !== null)
        ));
    }

    /**
     * The providers that can be called now, in order.
     *
     * @return array<int, AiProviderInterface>
     */
    public function availableProviders(): array
    {
        return array_values(array_filter($this->chain(), fn (AiProviderInterface $provider): bool => $provider->setupProblem() === null));
    }

    /**
     * Why no AI call can be made, or null when at least one provider is ready.
     */
    public function setupProblem(): ?string
    {
        if (! config('ai.enabled')) {
            return 'AI is turned off. Set AI_ENABLED=true in the .env file.';
        }

        $unknown = array_filter($this->configuredNames(), fn (string $name): bool => $this->manager->resolveName($name) === null);

        if ($unknown !== []) {
            return 'Unknown AI provider "' . implode('", "', $unknown) . '" in AI_PROVIDER or AI_FAILOVER. Use '
                . collect(array_keys(AiManager::PROVIDERS))->join(', ', ' or ') . '.';
        }

        $chain = $this->chain();

        if ($chain === []) {
            return $this->manager->isManagedInApp()
                ? 'AI is not set up yet. Turn on a provider on the AI Settings page.'
                : 'AI is not set up yet. Set AI_PROVIDER in the .env file.';
        }

        return $this->availableProviders() === [] ? $chain[0]->setupProblem() : null;
    }

    /**
     * Longest one request can take when every provider is tried, in seconds.
     */
    public function maxDurationSeconds(): int
    {
        return array_sum(array_map(fn (AiProviderInterface $provider): int => $provider->maxDurationSeconds(), $this->availableProviders()));
    }

    /**
     * Asks the first ready provider for JSON, failing over to the next one when it cannot answer.
     *
     * @param array<int, AiDocument> $documents
     * @param array<string, mixed> $schema
     * @param (Closure(AiProviderInterface, AiUnavailableException): void)|null $onFailure Called for each provider that failed, so its attempt can be logged
     *
     * @throws AiUnavailableException when no provider is ready, or every provider failed
     */
    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens, ?Closure $onFailure = null): AiResponse
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        $failures = [];

        foreach ($this->availableProviders() as $provider) {
            try {
                return $provider->generateJson($instructions, $prompt, $documents, $schema, $maxOutputTokens);
            } catch (AiUnavailableException $e) {
                $failures[$provider->label()] = $e;

                if ($onFailure !== null) {
                    $onFailure($provider, $e);
                }
            }
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
