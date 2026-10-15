<div>
    @if(session()->has('ai_settings_message'))
        <div class="alert alert-success">
            <i class="fas fa-check mr-1"></i> {{ session('ai_settings_message') }}
        </div>
    @endif

    <div class="callout {{ $managedInApp ? 'callout-info' : 'callout-warning' }}">
        @if($managedInApp)
            <p class="mb-0">
                These settings choose the AI providers. The API keys stay in the <code>.env</code> file.
            </p>
        @else
            <p class="mb-0">
                The AI providers still follow the <code>.env</code> file (<code>AI_PROVIDER</code>, <code>AI_FAILOVER</code>
                and the model settings). Saving this page takes over from them. The API keys always stay in the <code>.env</code> file.
            </p>
        @endif
    </div>

    @if($aiProblem)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $aiProblem }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Providers, in the order they are tried</h3>
            <div class="card-tools">
                <div wire:loading class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i> Working…</div>
            </div>
        </div>
        <div class="card-body p-0">
            @foreach($providers as $index => $row)
                @php
                    $driver = $drivers[$index];
                    $check = $checks[$row['name']] ?? null;
                @endphp
                <div wire:key="ai-provider-{{ $row['name'] }}" class="p-3 {{ $loop->last ? '' : 'border-bottom' }} {{ $row['is_enabled'] ? '' : 'bg-light' }}">
                    <div class="d-flex align-items-center flex-wrap mb-2">
                        <div class="btn-group-vertical btn-group-sm mr-3">
                            <button type="button" class="btn btn-default py-0" wire:click="moveUp({{ $index }})" @if($loop->first) disabled @endif title="Try earlier">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-default py-0" wire:click="moveDown({{ $index }})" @if($loop->last) disabled @endif title="Try later">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="custom-control custom-switch mr-3">
                            <input type="checkbox" class="custom-control-input" id="ai_enabled_{{ $row['name'] }}"
                                   wire:model="providers.{{ $index }}.is_enabled">
                            <label class="custom-control-label h5 mb-0" for="ai_enabled_{{ $row['name'] }}">
                                {{ $index + 1 }}. {{ $driver->label() }}
                            </label>
                        </div>
                        @if($driver->setupProblem() === null)
                            <span class="badge badge-success"><i class="fas fa-key mr-1"></i>Key set</span>
                        @else
                            <span class="badge badge-secondary" title="{{ $driver->setupProblem() }}"><i class="fas fa-key mr-1"></i>No key</span>
                        @endif
                        <div class="ml-auto">
                            <button type="button" class="btn btn-default btn-sm" wire:click="loadModels({{ $index }})"
                                    wire:loading.attr="disabled" @if($driver->setupProblem() !== null) disabled @endif>
                                <i class="fas fa-list mr-1"></i> Load models
                            </button>
                            <button type="button" class="btn btn-default btn-sm" wire:click="testConnection({{ $index }})"
                                    wire:loading.attr="disabled" @if($driver->setupProblem() !== null) disabled @endif>
                                <i class="fas fa-plug mr-1"></i> Test connection
                            </button>
                        </div>
                    </div>

                    @if($driver->setupProblem() !== null)
                        <p class="small text-muted mb-2">{{ $driver->setupProblem() }} Until then it is skipped.</p>
                    @endif

                    @if($check)
                        <div class="alert {{ $check['ok'] ? 'alert-success' : 'alert-danger' }} py-1 px-2 small mb-2">
                            <i class="fas {{ $check['ok'] ? 'fa-check' : 'fa-times' }} mr-1"></i> {{ $check['message'] }}
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="col-lg-3 col-md-6 form-group mb-2">
                            <label for="ai_model_{{ $row['name'] }}" class="small font-weight-bold mb-1">Model</label>
                            <input type="text" id="ai_model_{{ $row['name'] }}" list="ai_models_{{ $row['name'] }}"
                                   wire:model.defer="providers.{{ $index }}.model"
                                   class="form-control form-control-sm @error('providers.' . $index . '.model') is-invalid @enderror">
                            <datalist id="ai_models_{{ $row['name'] }}">
                                @foreach($modelLists[$row['name']] ?? [] as $modelId)
                                    <option value="{{ $modelId }}">
                                @endforeach
                            </datalist>
                            @error('providers.' . $index . '.model')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-lg-2 col-md-6 form-group mb-2">
                            <label for="ai_effort_{{ $row['name'] }}" class="small font-weight-bold mb-1">Thinking effort</label>
                            <select id="ai_effort_{{ $row['name'] }}" wire:model.defer="providers.{{ $index }}.effort"
                                    class="form-control form-control-sm @error('providers.' . $index . '.effort') is-invalid @enderror">
                                <option value="">Model default</option>
                                @foreach($driver->effortLevels() as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('providers.' . $index . '.effort')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-lg-2 col-md-6 form-group mb-2">
                            <label for="ai_input_price_{{ $row['name'] }}" class="small font-weight-bold mb-1">Input price</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input type="number" step="0.0001" min="0" id="ai_input_price_{{ $row['name'] }}"
                                       wire:model.defer="providers.{{ $index }}.input_price" placeholder="per 1M tokens"
                                       class="form-control @error('providers.' . $index . '.input_price') is-invalid @enderror">
                            </div>
                            @error('providers.' . $index . '.input_price')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-lg-2 col-md-6 form-group mb-2">
                            <label for="ai_output_price_{{ $row['name'] }}" class="small font-weight-bold mb-1">Output price</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input type="number" step="0.0001" min="0" id="ai_output_price_{{ $row['name'] }}"
                                       wire:model.defer="providers.{{ $index }}.output_price" placeholder="per 1M tokens"
                                       class="form-control @error('providers.' . $index . '.output_price') is-invalid @enderror">
                            </div>
                            @error('providers.' . $index . '.output_price')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        @if(in_array($row['name'], $editableUrl, true))
                            <div class="col-lg-3 col-md-12 form-group mb-2">
                                <label for="ai_base_url_{{ $row['name'] }}" class="small font-weight-bold mb-1">Server address</label>
                                <input type="url" id="ai_base_url_{{ $row['name'] }}" wire:model.defer="providers.{{ $index }}.base_url"
                                       placeholder="https://api.openai.com/v1/"
                                       class="form-control form-control-sm @error('providers.' . $index . '.base_url') is-invalid @enderror">
                                @error('providers.' . $index . '.base_url')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">OpenAI, OpenRouter, Groq, DeepSeek or a local Ollama.</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer d-flex align-items-center flex-wrap">
            <span class="small text-muted mr-auto">
                Prices are US dollars per million tokens, from the provider's price page. They give the
                estimated cost on the AI Usage page. Lower effort uses fewer output tokens.
            </span>
            @if($managedInApp)
                <button type="button" class="btn btn-default mr-2" wire:click="resetToEnv"
                        onclick="confirm('Forget these settings and go back to the .env file?') || event.stopImmediatePropagation()">
                    <i class="fas fa-undo mr-1"></i> Use .env file
                </button>
            @endif
            <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                <i class="fas fa-save mr-1"></i> Save AI Settings
            </button>
        </div>
    </div>
</div>
