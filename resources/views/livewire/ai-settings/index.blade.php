<div>
    @if(session()->has('ai_settings_message'))
        <div class="alert alert-success">
            <i class="fas fa-check mr-1"></i> {{ session('ai_settings_message') }}
        </div>
    @endif

    <div class="callout {{ $managedInApp ? 'callout-info' : 'callout-warning' }}">
        <p class="mb-1">
            The first provider turned on is the <strong>main</strong> provider. When it cannot answer (down, busy,
            out of quota or missing its key), the <strong>backups</strong> below it are tried in order.
        </p>
        <p class="mb-0">
            Only the API keys are in the <code>.env</code> file:
            <code>GEMINI_API_KEY</code>, <code>ANTHROPIC_API_KEY</code>, <code>OPENAI_API_KEY</code>, <code>GROQ_API_KEY</code> and <code>QWEN_API_KEY</code>.
            @unless($managedInApp)
                Nothing is saved here yet, so the defaults apply. Save this page to make your own choices.
            @endunless
        </p>
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
                    $enabledSoFar ??= 0;
                    $driver = $drivers[$index];
                    $check = $checks[$row['name']] ?? null;
                    $role = null;
                    if ($row['is_enabled']) {
                        $role = $enabledSoFar === 0 ? 'Main' : 'Backup ' . $enabledSoFar;
                        $enabledSoFar++;
                    }
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
                                   wire:model.live="providers.{{ $index }}.is_enabled">
                            <label class="custom-control-label h5 mb-0" for="ai_enabled_{{ $row['name'] }}">
                                {{ $index + 1 }}. {{ $driver->label() }}
                            </label>
                        </div>
                        @if($role)
                            <span class="badge {{ $role === 'Main' ? 'badge-primary' : 'badge-info' }} mr-1">{{ $role }}</span>
                        @endif
                        @if($driver->canRead('application/pdf'))
                            <span class="badge badge-light border mr-1" title="Can read PO PDFs"><i class="fas fa-file-pdf mr-1"></i>Scans PDFs</span>
                        @else
                            <span class="badge badge-light border mr-1" title="Used for the assistant, insights and mapping help. PO scans go to the next provider that reads PDFs."><i class="fas fa-font mr-1"></i>Text only</span>
                        @endif
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

                    @if(isset($quotas[$row['name']]))
                        <p class="small text-warning mb-2">
                            <i class="fas fa-hourglass-half mr-1"></i>{{ $quotas[$row['name']]['reason'] }}
                            It is skipped until {{ \App\Services\Ai\AiQuota::resetLabel(\Carbon\Carbon::parse($quotas[$row['name']]['until'])) }}.
                            <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click="tryAgainNow('{{ $row['name'] }}')">Try it again now</button>
                        </p>
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
                                   wire:model="providers.{{ $index }}.model"
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
                            <select id="ai_effort_{{ $row['name'] }}" wire:model="providers.{{ $index }}.effort"
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
                                       wire:model="providers.{{ $index }}.input_price" placeholder="per 1M tokens"
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
                                       wire:model="providers.{{ $index }}.output_price" placeholder="per 1M tokens"
                                       class="form-control @error('providers.' . $index . '.output_price') is-invalid @enderror">
                            </div>
                            @error('providers.' . $index . '.output_price')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        @if(in_array($row['name'], $editableUrl, true))
                            <div class="col-lg-3 col-md-12 form-group mb-2">
                                <label for="ai_base_url_{{ $row['name'] }}" class="small font-weight-bold mb-1">Server address</label>
                                <input type="url" id="ai_base_url_{{ $row['name'] }}" wire:model="providers.{{ $index }}.base_url"
                                       placeholder="{{ $defaultUrls[$row['name']] ?? '' }}"
                                       class="form-control form-control-sm @error('providers.' . $index . '.base_url') is-invalid @enderror">
                                @error('providers.' . $index . '.base_url')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    @switch($row['name'])
                                        @case('qwen')
                                            International keys: dashscope-intl.aliyuncs.com. China keys: dashscope.aliyuncs.com (both end in /compatible-mode/v1).
                                            @break
                                        @case('groq')
                                            Leave as is unless Groq gives you another address.
                                            @break
                                        @default
                                            OpenAI, OpenRouter, DeepSeek or a local Ollama.
                                    @endswitch
                                </small>
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
            <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                <i class="fas fa-save mr-1"></i> Save AI Settings
            </button>
        </div>
    </div>
</div>
