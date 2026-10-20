<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Models\AiProviderSetting;
use App\Services\Ai\Providers\AnthropicProvider;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Knows which AI providers exist and builds them from config/ai.php, with the model, effort and
 * server address saved on the AI Settings page laid over it. To add a provider, write a class
 * implementing AiProviderInterface, add it to PROVIDERS and give it a config block.
 */
class AiManager
{
    /** @var array<string, class-string<AiProviderInterface>> */
    public const PROVIDERS = [
        GeminiProvider::NAME    => GeminiProvider::class,
        AnthropicProvider::NAME => AnthropicProvider::class,
        OpenAiProvider::NAME    => OpenAiProvider::class,
    ];

    /** Other names accepted in AI_PROVIDER and AI_FAILOVER. */
    public const ALIASES = [
        'claude'            => AnthropicProvider::NAME,
        'google'            => GeminiProvider::NAME,
        'openai-compatible' => OpenAiProvider::NAME,
    ];

    /** Providers whose server address can be changed on the AI Settings page. */
    public const EDITABLE_URL = [OpenAiProvider::NAME];

    /** @var Collection<string, AiProviderSetting>|null */
    private ?Collection $saved = null;

    /**
     * The canonical provider name, or null when the name is unknown.
     */
    public function resolveName(string $name): ?string
    {
        $name = strtolower(trim($name));
        $name = self::ALIASES[$name] ?? $name;

        return isset(self::PROVIDERS[$name]) ? $name : null;
    }

    /**
     * The rows saved on the AI Settings page, in failover order and keyed by provider name. Empty
     * until the page has been saved, and then the .env order and models no longer apply.
     *
     * @return Collection<string, AiProviderSetting>
     */
    public function savedSettings(): Collection
    {
        return $this->saved ??= AiProviderSetting::query()
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->filter(fn (AiProviderSetting $setting): bool => isset(self::PROVIDERS[$setting->name]))
            ->keyBy('name');
    }

    public function isManagedInApp(): bool
    {
        return $this->savedSettings()->isNotEmpty();
    }

    /**
     * Builds a provider. Without overrides it uses the saved settings, or the .env file when none
     * are saved; the AI Settings page passes its unsaved form values to test them.
     *
     * @param array{model?: string|null, effort?: string|null, base_url?: string|null}|null $overrides
     */
    public function provider(string $name, ?array $overrides = null): AiProviderInterface
    {
        $resolved = $this->resolveName($name)
            ?? throw new InvalidArgumentException("Unknown AI provider [{$name}].");

        return app()->make(self::PROVIDERS[$resolved], [
            'config' => $this->configFor($resolved, $overrides ?? $this->savedSettings()->get($resolved)?->only(['model', 'effort', 'base_url'])),
        ]);
    }

    /**
     * Estimated cost in US dollars of a call, from the prices saved on the AI Settings page, or
     * null when the provider has no price.
     */
    /**
     * @param int $cachedInputTokens input tokens read from the prompt cache, billed at the provider's cache read rate
     * @param int $cacheWriteTokens input tokens written to the prompt cache, billed at its cache write rate
     */
    public function costOf(string $provider, int $inputTokens, int $outputTokens, int $cachedInputTokens = 0, int $cacheWriteTokens = 0): ?float
    {
        return $this->savedSettings()->get($provider)?->costOf($inputTokens, $outputTokens, $cachedInputTokens, $cacheWriteTokens);
    }

    /**
     * A saved blank effort means "the model's default", so it replaces the .env value too.
     *
     * @param array{model?: string|null, effort?: string|null, base_url?: string|null}|null $overrides
     * @return array<string, mixed>
     */
    private function configFor(string $name, ?array $overrides): array
    {
        $config = (array) config("ai.providers.{$name}", []);

        if ($overrides === null) {
            return $config;
        }

        if (filled($overrides['model'] ?? null)) {
            $config['model'] = trim((string) $overrides['model']);
        }

        if (array_key_exists('effort', $overrides)) {
            $config['effort'] = $overrides['effort'];
        }

        if (\in_array($name, self::EDITABLE_URL, true) && filled($overrides['base_url'] ?? null)) {
            $config['url'] = trim((string) $overrides['base_url']);
        }

        return $config;
    }
}
