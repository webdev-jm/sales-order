<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Services\Ai\Providers\AnthropicProvider;
use App\Services\Ai\Providers\GeminiProvider;
use InvalidArgumentException;

/**
 * Knows which AI providers exist and builds them from config/ai.php. To add a provider, write a
 * class implementing AiProviderInterface, add it to PROVIDERS and give it a config block.
 */
class AiManager
{
    /** @var array<string, class-string<AiProviderInterface>> */
    public const PROVIDERS = [
        GeminiProvider::NAME    => GeminiProvider::class,
        AnthropicProvider::NAME => AnthropicProvider::class,
    ];

    /** Other names accepted in AI_PROVIDER and AI_FAILOVER. */
    public const ALIASES = [
        'claude' => AnthropicProvider::NAME,
        'google' => GeminiProvider::NAME,
    ];

    /**
     * The canonical provider name, or null when the name is unknown.
     */
    public function resolveName(string $name): ?string
    {
        $name = strtolower(trim($name));
        $name = self::ALIASES[$name] ?? $name;

        return isset(self::PROVIDERS[$name]) ? $name : null;
    }

    public function provider(string $name): AiProviderInterface
    {
        $resolved = $this->resolveName($name)
            ?? throw new InvalidArgumentException("Unknown AI provider [{$name}].");

        return app()->make(self::PROVIDERS[$resolved], [
            'config' => (array) config("ai.providers.{$resolved}", []),
        ]);
    }
}
