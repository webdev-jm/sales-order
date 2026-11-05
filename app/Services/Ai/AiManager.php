<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Models\AiFeatureSetting;
use App\Models\AiProviderSetting;
use App\Services\Ai\Providers\AnthropicProvider;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\GroqProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use App\Services\Ai\Providers\QwenProvider;
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
        GroqProvider::NAME      => GroqProvider::class,
        QwenProvider::NAME      => QwenProvider::class,
    ];

    /** Other names accepted in config/ai.php's provider and failover. */
    public const ALIASES = [
        'claude'            => AnthropicProvider::NAME,
        'google'            => GeminiProvider::NAME,
        'openai-compatible' => OpenAiProvider::NAME,
    ];

    /** Providers whose server address can be changed on the AI Settings page. */
    public const EDITABLE_URL = [OpenAiProvider::NAME, GroqProvider::NAME, QwenProvider::NAME];

    /** @var Collection<string, AiProviderSetting>|null */
    private ?Collection $saved = null;

    /** @var Collection<string, AiFeatureSetting>|null */
    private ?Collection $features = null;

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
     * until the page has been saved; until then the defaults in config/ai.php apply.
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
     * The AI functions given their own providers on the AI Settings page, keyed by function.
     *
     * @return Collection<string, AiFeatureSetting>
     */
    public function featureSettings(): Collection
    {
        return $this->features ??= AiFeatureSetting::query()
            ->get()
            ->filter(fn (AiFeatureSetting $setting): bool => isset(AiFeatureSetting::FEATURES[$setting->feature]) && $setting->providers !== [])
            ->keyBy('feature');
    }

    /**
     * The providers and models one AI function tries in turn, or null when it follows the main provider order.
     *
     * @return array<int, array{name: string, model: string|null, input_price: float|null, output_price: float|null}>|null
     */
    public function featureSteps(?string $feature): ?array
    {
        if ($feature === null) {
            return null;
        }

        $steps = (array) $this->featureSettings()->get($feature)?->providers;

        return $steps !== [] ? array_values($steps) : null;
    }

    /**
     * A provider with its saved settings, running another model than its own when one is given.
     */
    public function providerWithModel(string $name, ?string $model): AiProviderInterface
    {
        if (blank($model)) {
            return $this->provider($name);
        }

        $resolved = $this->resolveName($name) ?? $name;
        $saved = $this->savedSettings()->get($resolved)?->only(['model', 'effort', 'base_url']) ?? [];

        return $this->provider($name, ['model' => trim((string) $model)] + $saved);
    }

    /**
     * Builds a provider. Without overrides it uses the saved settings, or the defaults in
     * config/ai.php when none are saved; the AI Settings page passes its unsaved form values to test them.
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
     * null when the provider has no price. A function that lists this provider and model with its
     * own prices is charged those instead.
     *
     * @param int $cachedInputTokens input tokens read from the prompt cache, billed at the provider's cache read rate
     * @param int $cacheWriteTokens input tokens written to the prompt cache, billed at its cache write rate
     */
    public function costOf(string $provider, int $inputTokens, int $outputTokens, int $cachedInputTokens = 0, int $cacheWriteTokens = 0, ?string $feature = null, ?string $model = null): ?float
    {
        $prices = $this->savedSettings()->get($provider);
        $step = $feature !== null && $model !== null ? $this->featureSettings()->get($feature)?->stepFor($provider, $model) : null;

        if ($step !== null && (($step['input_price'] ?? null) !== null || ($step['output_price'] ?? null) !== null)) {
            $prices = new AiProviderSetting([
                'name'         => $provider,
                'input_price'  => $step['input_price'] ?? $prices?->input_price,
                'output_price' => $step['output_price'] ?? $prices?->output_price,
            ]);
        }

        return $prices?->costOf($inputTokens, $outputTokens, $cachedInputTokens, $cacheWriteTokens);
    }

    /**
     * A saved blank effort means "the model's default", so it replaces the config default too.
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
