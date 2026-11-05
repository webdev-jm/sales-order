<?php

namespace App\Http\Livewire\AiSettings;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Models\AiFeatureSetting;
use App\Models\AiUsageLog;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiQuota;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The second part of the AI Settings page: the provider and model each AI function uses. A function either
 * follows the main provider order above it, or gets its own list (main, then backups), each with a model
 * and, when that model costs something else, its own token prices. Functions that read PDF files can only
 * pick providers that read PDFs.
 */
class Features extends Component
{
    /** Most providers one function can try in turn. */
    public const MAX_STEPS = 6;

    /** @var array<string, array{own: bool, steps: array<int, array{name: string, model: string, input_price: string, output_price: string}>}> */
    public array $features = [];

    /** @var array<string, array<int, string>> provider name => model IDs it offers */
    public array $modelLists = [];

    /** @var array<string, string> provider name => why its models could not be listed */
    public array $modelListErrors = [];

    protected $messages = [
        'features.*.steps.required'           => 'Add at least one provider, or turn off "Own providers".',
        'features.*.steps.max'                => 'Use at most :max providers.',
        'features.*.steps.*.name.required'    => 'Choose a provider.',
        'features.*.steps.*.name.in'          => 'Choose one of the listed providers.',
        'features.*.steps.*.model.max'        => 'The model name is too long.',
        'features.*.steps.*.input_price.numeric'  => 'Enter the price in US dollars per million tokens, or leave it blank.',
        'features.*.steps.*.input_price.min'      => 'The price cannot be negative.',
        'features.*.steps.*.output_price.numeric' => 'Enter the price in US dollars per million tokens, or leave it blank.',
        'features.*.steps.*.output_price.min'     => 'The price cannot be negative.',
    ];

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->loadFeatures();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = ['features' => ['array']];

        foreach ($this->features as $feature => $row) {
            if (! $row['own']) {
                continue;
            }

            $rules["features.{$feature}.steps"] = ['required', 'array', 'max:' . self::MAX_STEPS];
            $rules["features.{$feature}.steps.*.name"] = ['required', Rule::in(array_keys(AiManager::PROVIDERS))];
            $rules["features.{$feature}.steps.*.model"] = ['nullable', 'string', 'max:255'];
            $rules["features.{$feature}.steps.*.input_price"] = ['nullable', 'numeric', 'min:0', 'max:100000'];
            $rules["features.{$feature}.steps.*.output_price"] = ['nullable', 'numeric', 'min:0', 'max:100000'];
        }

        return $rules;
    }

    public function addStep(string $feature): void
    {
        if (! isset($this->features[$feature]) || \count($this->features[$feature]['steps']) >= self::MAX_STEPS) {
            return;
        }

        $used = array_column($this->features[$feature]['steps'], 'name');
        $choices = $this->choicesFor($feature);
        $name = collect($choices)->first(fn (string $choice): bool => ! \in_array($choice, $used, true)) ?? ($choices[0] ?? '');

        $this->features[$feature]['steps'][] = $this->blankStep($name, $name !== '' ? app(AiManager::class)->provider($name)->model() : '');
    }

    public function removeStep(string $feature, int $index): void
    {
        if (! isset($this->features[$feature]['steps'][$index])) {
            return;
        }

        array_splice($this->features[$feature]['steps'], $index, 1);
        $this->resetValidation();
    }

    public function moveStep(string $feature, int $index, int $by): void
    {
        $to = $index + $by;

        if (! isset($this->features[$feature]['steps'][$index], $this->features[$feature]['steps'][$to])) {
            return;
        }

        $steps = $this->features[$feature]['steps'];
        [$steps[$index], $steps[$to]] = [$steps[$to], $steps[$index]];
        $this->features[$feature]['steps'] = $steps;
    }

    /**
     * Turning on "Own providers" starts the list from the main order the function follows now, so nothing
     * changes until a provider or model is changed. A new provider in a row starts on that provider's own
     * model, so the model box never keeps another provider's model.
     */
    public function updatedFeatures(mixed $value, string $key): void
    {
        if (preg_match('/^([a-z_]+)\.own$/', $key, $match) === 1 && isset($this->features[$match[1]])) {
            $feature = $match[1];

            if ($this->features[$feature]['own'] && $this->features[$feature]['steps'] === []) {
                $this->features[$feature]['steps'] = array_map(
                    fn (AiProviderInterface $provider): array => $this->blankStep($provider->name(), $provider->model()),
                    array_slice($this->defaultChain($feature), 0, self::MAX_STEPS)
                );

                if ($this->features[$feature]['steps'] === []) {
                    $this->addStep($feature);
                }
            }

            $this->resetValidation();

            return;
        }

        if (preg_match('/^([a-z_]+)\.steps\.(\d+)\.name$/', $key, $match) === 1 && isset($this->features[$match[1]]['steps'][(int) $match[2]])) {
            $this->features[$match[1]]['steps'][(int) $match[2]]['model'] = isset(AiManager::PROVIDERS[$value])
                ? app(AiManager::class)->provider((string) $value)->model()
                : '';
        }
    }

    /**
     * Fills the model suggestions for one provider. The list is kept for a while, and costs no tokens.
     */
    public function loadModels(string $name): void
    {
        $this->authorizeAccess();

        $manager = app(AiManager::class);

        if ($manager->resolveName($name) === null) {
            return;
        }

        $provider = $manager->provider($name);
        $config = (array) config("ai.providers.{$name}", []);
        $cacheKey = 'ai-models:' . $name . ':' . hash('sha256', ($manager->savedSettings()->get($name)?->base_url ?? '') . '|' . ($config['url'] ?? '') . '|' . ($config['key'] ?? ''));

        try {
            $this->modelLists[$name] = Cache::remember(
                $cacheKey,
                now()->addMinutes((int) config('ai.model_list_cache_minutes', 60)),
                fn (): array => $provider->listModels()
            );
            unset($this->modelListErrors[$name]);
        } catch (AiUnavailableException $e) {
            $this->modelListErrors[$name] = $e->getMessage();
        }
    }

    public function tryAgainNow(string $name, string $model): void
    {
        $this->authorizeAccess();

        AiQuota::clear($name, $model);
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $this->trimModels();

        $this->withValidator(function (Validator $validator): void {
            $validator->after(fn (Validator $validator) => $this->checkSteps($validator));
        })->validate();

        $old = AiFeatureSetting::query()->get(['feature', 'providers'])->toArray();

        (new AiFeatureSetting())->getConnection()->transaction(function (): void {
            foreach ($this->features as $feature => $row) {
                if (! $row['own']) {
                    AiFeatureSetting::query()->where('feature', $feature)->delete();

                    continue;
                }

                AiFeatureSetting::updateOrCreate(['feature' => $feature], [
                    'providers'  => array_map(fn (array $step): array => [
                        'name'         => $step['name'],
                        'model'        => $step['model'] !== '' ? $step['model'] : null,
                        'input_price'  => $step['input_price'] !== '' ? (float) $step['input_price'] : null,
                        'output_price' => $step['output_price'] !== '' ? (float) $step['output_price'] : null,
                    ], array_values($row['steps'])),
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        activity('update')
            ->withProperties(['old' => $old, 'changes' => $this->features])
            ->log(':causer.firstname :causer.lastname has updated the AI function models');

        $this->loadFeatures();
        session()->flash('ai_features_message', 'AI function models saved. The next AI call uses them.');
    }

    /**
     * The main order changed on the same page, so the "follows the main order" summaries are redrawn.
     */
    #[On('ai-settings-saved')]
    public function refreshMainOrder(): void
    {
    }

    public function render(): View
    {
        $manager = app(AiManager::class);
        $providers = collect(array_keys(AiManager::PROVIDERS))->mapWithKeys(fn (string $name): array => [$name => $manager->provider($name)]);

        $quotas = [];

        foreach ($this->features as $feature => $row) {
            foreach ($row['own'] ? $row['steps'] : [] as $index => $step) {
                if (isset(AiManager::PROVIDERS[$step['name']]) && ($blocked = AiQuota::blocked($step['name'], $step['model'] !== '' ? $step['model'] : $providers[$step['name']]->model())) !== null) {
                    $quotas[$feature][$index] = $blocked;
                }
            }
        }

        return view('livewire.ai-settings.features', [
            'labels'      => AiUsageLog::FEATURES,
            'details'     => AiFeatureSetting::FEATURES,
            'providers'   => $providers,
            'choices'     => collect(array_keys($this->features))->mapWithKeys(fn (string $feature): array => [$feature => $this->choicesFor($feature)])->all(),
            'mainOrders'  => collect(array_keys($this->features))->mapWithKeys(fn (string $feature): array => [$feature => $this->defaultChain($feature)])->all(),
            'quotas'      => $quotas,
            'maxSteps'    => self::MAX_STEPS,
        ]);
    }

    /**
     * The same provider twice with the same model would only repeat a call that just failed, and a function
     * that reads PDFs cannot use a provider that does not read them.
     */
    private function checkSteps(Validator $validator): void
    {
        $manager = app(AiManager::class);

        foreach ($this->features as $feature => $row) {
            if (! $row['own']) {
                continue;
            }

            $seen = [];

            foreach ($row['steps'] as $index => $step) {
                if (! isset(AiManager::PROVIDERS[$step['name']])) {
                    continue;
                }

                $provider = $manager->provider($step['name']);
                $model = $step['model'] !== '' ? $step['model'] : $provider->model();

                if (AiFeatureSetting::readsPdf($feature) && ! $provider->canRead('application/pdf')) {
                    $validator->errors()->add("features.{$feature}.steps.{$index}.name", $provider->label() . ' cannot read PDF files. Choose a provider that scans PDFs.');
                }

                if (isset($seen[$step['name'] . '|' . $model])) {
                    $validator->errors()->add("features.{$feature}.steps.{$index}.model", 'This provider and model are already in the list.');
                }

                $seen[$step['name'] . '|' . $model] = true;
            }
        }
    }

    private function trimModels(): void
    {
        foreach ($this->features as $feature => $row) {
            foreach ($row['steps'] as $index => $step) {
                $this->features[$feature]['steps'][$index]['model'] = trim((string) $step['model']);
            }
        }
    }

    /**
     * The providers that can be picked for a function: every provider, or only those that read PDFs.
     *
     * @return array<int, string>
     */
    private function choicesFor(string $feature): array
    {
        $manager = app(AiManager::class);

        return array_values(array_filter(
            array_keys(AiManager::PROVIDERS),
            fn (string $name): bool => ! AiFeatureSetting::readsPdf($feature) || $manager->provider($name)->canRead('application/pdf')
        ));
    }

    /**
     * The providers a function tries when it follows the main order: the ones turned on, and for a PDF
     * function only those that read PDFs.
     *
     * @return array<int, AiProviderInterface>
     */
    private function defaultChain(string $feature): array
    {
        $chain = app(AiClient::class)->chain();

        if (! AiFeatureSetting::readsPdf($feature)) {
            return $chain;
        }

        return array_values(array_filter($chain, fn (AiProviderInterface $provider): bool => $provider->canRead('application/pdf')));
    }

    /**
     * @return array{name: string, model: string, input_price: string, output_price: string}
     */
    private function blankStep(string $name, string $model): array
    {
        return ['name' => $name, 'model' => $model, 'input_price' => '', 'output_price' => ''];
    }

    private function loadFeatures(): void
    {
        $saved = app(AiManager::class)->featureSettings();

        $this->features = [];

        foreach (array_keys(AiFeatureSetting::FEATURES) as $feature) {
            $steps = (array) $saved->get($feature)?->providers;

            $this->features[$feature] = [
                'own'   => $steps !== [],
                'steps' => array_map(fn (array $step): array => [
                    'name'         => (string) ($step['name'] ?? ''),
                    'model'        => (string) ($step['model'] ?? ''),
                    'input_price'  => isset($step['input_price']) ? (string) $step['input_price'] : '',
                    'output_price' => isset($step['output_price']) ? (string) $step['output_price'] : '',
                ], array_values($steps)),
            ];
        }
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->can('ai settings manage'), 403);
    }
}
