<?php

namespace App\Http\Livewire\AiSettings;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Models\AiProviderSetting;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * The AI Settings page: which providers answer, in what order, with which model, effort and
 * token prices. Saving it replaces AI_PROVIDER, AI_FAILOVER and the model settings in the .env
 * file; the API keys stay in the .env file. The model list and the connection test cost no tokens.
 */
class Index extends Component
{
    /** @var array<int, array{name: string, is_enabled: bool, model: string, effort: string, base_url: string, input_price: string, output_price: string}> */
    public array $providers = [];

    /** @var array<string, array<int, string>> provider name => model IDs it offers */
    public array $modelLists = [];

    /** @var array<string, array{ok: bool, message: string}> provider name => result of the last check */
    public array $checks = [];

    protected $messages = [
        'providers.*.model.required'  => 'Enter the model to use.',
        'providers.*.effort.in'       => 'Choose one of the listed effort levels.',
        'providers.*.base_url.url'    => 'Enter the full server address, starting with https://.',
        'providers.*.input_price.numeric'  => 'Enter the price in US dollars per million tokens, or leave it blank.',
        'providers.*.input_price.min'      => 'The price cannot be negative.',
        'providers.*.output_price.numeric' => 'Enter the price in US dollars per million tokens, or leave it blank.',
        'providers.*.output_price.min'     => 'The price cannot be negative.',
    ];

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->loadProviders();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'providers'                => 'array',
            'providers.*.name'         => ['required', 'distinct', Rule::in(array_keys(AiManager::PROVIDERS))],
            'providers.*.is_enabled'   => 'boolean',
            'providers.*.model'        => 'required|string|max:255',
            'providers.*.base_url'     => 'nullable|url|max:255',
            'providers.*.input_price'  => 'nullable|numeric|min:0|max:100000',
            'providers.*.output_price' => 'nullable|numeric|min:0|max:100000',
        ];

        foreach ($this->providers as $index => $row) {
            $levels = isset(AiManager::PROVIDERS[$row['name'] ?? '']) ? $this->providerFor($index)->effortLevels() : [];
            $rules["providers.{$index}.effort"] = ['nullable', Rule::in($levels)];
        }

        return $rules;
    }

    public function moveUp(int $index): void
    {
        $this->swap($index, $index - 1);
    }

    public function moveDown(int $index): void
    {
        $this->swap($index, $index + 1);
    }

    /**
     * Fills the model suggestions for one provider, kept for a while because the list rarely changes.
     */
    public function loadModels(int $index): void
    {
        $this->authorizeAccess();

        $name     = $this->providers[$index]['name'] ?? '';
        $provider = $this->providerFor($index);
        $config   = (array) config("ai.providers.{$name}", []);
        $cacheKey = 'ai-models:' . $name . ':' . hash('sha256', ($this->providers[$index]['base_url'] ?? '') . '|' . ($config['url'] ?? '') . '|' . ($config['key'] ?? ''));

        try {
            $this->modelLists[$name] = Cache::remember(
                $cacheKey,
                now()->addMinutes((int) config('ai.model_list_cache_minutes', 60)),
                fn (): array => $provider->listModels()
            );
            $this->checks[$name] = ['ok' => true, 'message' => \count($this->modelLists[$name]) . ' models found. Pick one in the Model box.'];
        } catch (AiUnavailableException $e) {
            $this->checks[$name] = ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Checks the key and the model typed in the form, before it is saved.
     */
    public function testConnection(int $index): void
    {
        $this->authorizeAccess();

        $name = $this->providers[$index]['name'] ?? '';

        try {
            $this->checks[$name] = ['ok' => true, 'message' => $this->providerFor($index)->checkConnection()];
        } catch (AiUnavailableException $e) {
            $this->checks[$name] = ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $this->validate();

        $old = AiProviderSetting::query()->orderBy('priority')->get()->toArray();

        (new AiProviderSetting())->getConnection()->transaction(function (): void {
            foreach (array_values($this->providers) as $index => $row) {
                AiProviderSetting::updateOrCreate(['name' => $row['name']], [
                    'is_enabled'   => (bool) $row['is_enabled'],
                    'priority'     => $index + 1,
                    'model'        => trim($row['model']),
                    'effort'       => $row['effort'] !== '' ? $row['effort'] : null,
                    'base_url'     => \in_array($row['name'], AiManager::EDITABLE_URL, true) && trim($row['base_url']) !== '' ? trim($row['base_url']) : null,
                    'input_price'  => $row['input_price'] !== '' ? $row['input_price'] : null,
                    'output_price' => $row['output_price'] !== '' ? $row['output_price'] : null,
                    'updated_by'   => auth()->id(),
                ]);
            }
        });

        activity('update')
            ->withProperties(['old' => $old, 'changes' => $this->providers])
            ->log(':causer.firstname :causer.lastname has updated the AI settings');

        $this->loadProviders();
        session()->flash('ai_settings_message', 'AI settings saved. New scans use them right away.');
    }

    /**
     * Forgets the saved settings, so AI_PROVIDER, AI_FAILOVER and the .env models apply again.
     */
    public function resetToEnv(): void
    {
        $this->authorizeAccess();

        AiProviderSetting::query()->delete();

        activity('update')->log(':causer.firstname :causer.lastname has reset the AI settings to the .env file');

        $this->checks = [];
        $this->loadProviders();
        session()->flash('ai_settings_message', 'AI settings reset. The .env file applies again.');
    }

    public function render(): View
    {
        $providers = [];

        foreach ($this->providers as $index => $row) {
            $providers[$index] = $this->providerFor($index);
        }

        return view('livewire.ai-settings.index', [
            'drivers'      => $providers,
            'managedInApp' => app(AiManager::class)->isManagedInApp(),
            'editableUrl'  => AiManager::EDITABLE_URL,
            'aiProblem'    => app(AiClient::class)->setupProblem(),
        ]);
    }

    /**
     * Fills the form from the saved settings, or from the .env file when none are saved yet. Every
     * known provider is listed; the ones not in the order come last, turned off.
     */
    private function loadProviders(): void
    {
        $manager = app(AiManager::class);
        $saved   = $manager->savedSettings();
        $enabled = $saved->isNotEmpty()
            ? $saved->where('is_enabled', true)->keys()->all()
            : array_values(array_filter(app(AiClient::class)->configuredNames(), fn (string $name): bool => $manager->resolveName($name) !== null));

        $order = array_values(array_unique([...$saved->keys()->all(), ...$enabled, ...array_keys(AiManager::PROVIDERS)]));

        $this->providers = array_map(function (string $name) use ($manager, $saved, $enabled): array {
            $setting = $saved->get($name);

            return [
                'name'         => $name,
                'is_enabled'   => \in_array($name, $enabled, true),
                'model'        => $manager->provider($name)->model(),
                'effort'       => (string) ($setting ? $setting->effort : config("ai.providers.{$name}.effort")),
                'base_url'     => \in_array($name, AiManager::EDITABLE_URL, true) ? (string) ($setting?->base_url ?: config("ai.providers.{$name}.url")) : '',
                'input_price'  => $setting?->input_price !== null ? (string) $setting->input_price : '',
                'output_price' => $setting?->output_price !== null ? (string) $setting->output_price : '',
            ];
        }, $order);
    }

    /**
     * The provider as the form currently describes it, saved or not.
     */
    private function providerFor(int $index): AiProviderInterface
    {
        $row = $this->providers[$index];

        return app(AiManager::class)->provider($row['name'], [
            'model'    => $row['model'],
            'effort'   => $row['effort'] !== '' ? $row['effort'] : null,
            'base_url' => $row['base_url'],
        ]);
    }

    private function swap(int $from, int $to): void
    {
        if (!isset($this->providers[$from], $this->providers[$to])) {
            return;
        }

        [$this->providers[$from], $this->providers[$to]] = [$this->providers[$to], $this->providers[$from]];
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('ai settings manage'), 403);
    }
}
