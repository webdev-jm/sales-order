<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use Closure;
use Laravel\Ai\Ai;

/**
 * The AI entry point for the application. It sends a request to the first provider in the
 * failover order and, when that provider cannot answer (unreachable, overloaded, out of quota,
 * refusing the request or missing its key), fails over to the backups. The order is the one
 * saved on the AI Settings page, or the defaults in config/ai.php until that page is saved.
 * A provider without an API key is skipped, and so is one that cannot read the attached file.
 */
class AiClient
{
    /** Names given to the providers built for laravel/ai, so they never clash with its own. */
    public const LARAVEL_AI_PREFIX = 'sms-';

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
            return 'AI is turned off in config/ai.php.';
        }

        $unknown = array_filter($this->configuredNames(), fn (string $name): bool => $this->manager->resolveName($name) === null);

        if ($unknown !== []) {
            return 'Unknown AI provider "' . implode('", "', $unknown) . '" in config/ai.php. Use '
                . collect(array_keys(AiManager::PROVIDERS))->join(', ', ' or ') . '.';
        }

        $chain = $this->chain();

        if ($chain === []) {
            return 'AI is not set up yet. Turn on a provider on the AI Settings page.';
        }

        return $this->availableProviders() === [] ? $chain[0]->setupProblem() : null;
    }

    /**
     * The providers registered with laravel/ai, as the provider => model map an agent's prompt()
     * takes, in failover order. Each one is built on demand from its saved model and server, so
     * a change on the AI Settings page applies to the next call.
     *
     * @param array<int, AiProviderInterface>|null $providers Defaults to every ready provider
     * @return array<string, string>
     */
    public function laravelAiProviders(?array $providers = null): array
    {
        $chain = [];

        foreach ($providers ?? $this->availableProviders() as $provider) {
            $name = self::LARAVEL_AI_PREFIX . $provider->name();

            Ai::build(['name' => $name] + $provider->laravelAiConfig());

            $chain[$name] = $provider->model();
        }

        return $chain;
    }

    /**
     * The application provider behind a name from laravelAiProviders(), or a laravel/ai driver name.
     */
    public function providerForLaravelAi(string $name): ?AiProviderInterface
    {
        $own = str_starts_with($name, self::LARAVEL_AI_PREFIX) ? substr($name, \strlen(self::LARAVEL_AI_PREFIX)) : null;

        foreach ($this->chain() as $provider) {
            if ($provider->name() === $own || $provider->laravelAiConfig()['driver'] === $name) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Longest one request can take when every provider is tried, in seconds.
     */
    public function maxDurationSeconds(): int
    {
        return array_sum(array_map(fn (AiProviderInterface $provider): int => $provider->maxDurationSeconds(), $this->availableProviders()));
    }

    /**
     * The ready providers that can read every one of these files, in order.
     *
     * @param array<int, AiDocument> $documents
     * @return array<int, AiProviderInterface>
     */
    public function providersReading(array $documents): array
    {
        return array_values(array_filter($this->availableProviders(), function (AiProviderInterface $provider) use ($documents): bool {
            foreach ($documents as $document) {
                if (! $provider->canRead($document->mimeType)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Asks the first ready provider that can read the documents for JSON, failing over to the next one when it cannot answer.
     *
     * @param array<int, AiDocument> $documents
     * @param array<string, mixed> $schema
     * @param (Closure(AiProviderInterface, AiUnavailableException): void)|null $onFailure Called for each provider that failed, so its attempt can be logged
     *
     * @throws AiUnavailableException when no provider is ready, none reads the documents, or every provider failed
     */
    public function generateJson(string $instructions, string $prompt, array $documents, array $schema, int $maxOutputTokens, ?Closure $onFailure = null): AiResponse
    {
        if (($problem = $this->setupProblem()) !== null) {
            throw new AiUnavailableException($problem);
        }

        $providers = $this->providersReading($documents);

        if ($providers === []) {
            throw new AiUnavailableException($this->unreadableProblem($documents));
        }

        $failures = [];

        foreach ($providers as $provider) {
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

    /**
     * Why none of the turned-on providers can take these files, naming the ones that could.
     *
     * @param array<int, AiDocument> $documents
     */
    private function unreadableProblem(array $documents): string
    {
        $types = array_values(array_unique(array_map(fn (AiDocument $document): string => $document->mimeType, $documents)));
        $kind = $types === ['application/pdf'] ? 'PDF' : implode(', ', $types);

        $readers = [];

        foreach (array_keys(AiManager::PROVIDERS) as $name) {
            $provider = $this->manager->provider($name);

            if (array_filter($types, fn (string $type): bool => ! $provider->canRead($type)) === []) {
                $readers[] = $provider->label();
            }
        }

        return 'None of the AI providers turned on can read ' . $kind . ' files.'
            . ($readers !== [] ? ' Turn on ' . collect($readers)->join(', ', ' or ') . ' on the AI Settings page.' : '');
    }
}
