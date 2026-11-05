<div>
    @if(session()->has('ai_features_message'))
        <div class="alert alert-success">
            <i class="fas fa-check mr-1"></i> {{ session('ai_features_message') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Model for each AI function</h3>
            <div class="card-tools">
                <div wire:loading class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i> Working…</div>
            </div>
        </div>
        <div class="card-body p-0">
            @foreach($features as $feature => $row)
                <div wire:key="ai-feature-{{ $feature }}" class="p-3 {{ $loop->last ? '' : 'border-bottom' }}">
                    <div class="d-flex align-items-start flex-wrap">
                        <div class="mr-auto pr-3">
                            <h5 class="mb-0">
                                {{ $labels[$feature] ?? $feature }}
                                @if($details[$feature]['reads_pdf'])
                                    <span class="badge badge-light border ml-1 align-middle" title="Only providers that read PDF files can be picked"><i class="fas fa-file-pdf mr-1"></i>Reads PDFs</span>
                                @endif
                            </h5>
                            <p class="small text-muted mb-2">{{ $details[$feature]['description'] }}</p>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="ai_feature_own_{{ $feature }}"
                                   wire:model.live="features.{{ $feature }}.own">
                            <label class="custom-control-label" for="ai_feature_own_{{ $feature }}">Own providers and models</label>
                        </div>
                    </div>

                    @if(! $row['own'])
                        <p class="small mb-0">
                            <span class="text-muted">Follows the main order:</span>
                            @forelse($mainOrders[$feature] as $provider)
                                <span class="badge {{ $loop->first ? 'badge-primary' : 'badge-info' }}">{{ $provider->label() }} · {{ $provider->model() }}</span>
                                @unless($loop->last)<i class="fas fa-angle-right text-muted mx-1"></i>@endunless
                            @empty
                                <span class="text-warning">
                                    {{ $details[$feature]['reads_pdf'] ? 'no provider that reads PDFs is turned on above.' : 'no provider is turned on above.' }}
                                </span>
                            @endforelse
                        </p>
                    @else
                        @error("features.{$feature}.steps")
                            <div class="alert alert-danger py-1 px-2 small mb-2">{{ $message }}</div>
                        @enderror

                        <div class="table-responsive">
                            <table class="table table-sm mb-2">
                                <thead>
                                    <tr class="small text-muted">
                                        <th style="width: 5.5rem;"></th>
                                        <th style="min-width: 10rem;">Provider</th>
                                        <th style="min-width: 14rem;">Model</th>
                                        <th style="width: 9rem;">Input price</th>
                                        <th style="width: 9rem;">Output price</th>
                                        <th style="width: 2.5rem;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($row['steps'] as $index => $step)
                                        @php
                                            $driver = $providers[$step['name']] ?? null;
                                            $listId = 'ai_feature_models_' . $feature . '_' . $index;
                                        @endphp
                                        <tr wire:key="ai-feature-{{ $feature }}-step-{{ $index }}">
                                            <td class="align-middle text-nowrap">
                                                <div class="btn-group-vertical btn-group-sm mr-1 align-middle">
                                                    <button type="button" class="btn btn-default py-0" wire:click="moveStep('{{ $feature }}', {{ $index }}, -1)" @if($loop->first) disabled @endif title="Try earlier">
                                                        <i class="fas fa-chevron-up"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-default py-0" wire:click="moveStep('{{ $feature }}', {{ $index }}, 1)" @if($loop->last) disabled @endif title="Try later">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </div>
                                                <span class="badge {{ $loop->first ? 'badge-primary' : 'badge-info' }}">{{ $loop->first ? 'Main' : 'Backup ' . $index }}</span>
                                            </td>
                                            <td>
                                                <select wire:model.live="features.{{ $feature }}.steps.{{ $index }}.name"
                                                        aria-label="Provider"
                                                        class="form-control form-control-sm @error("features.{$feature}.steps.{$index}.name") is-invalid @enderror">
                                                    <option value="">Choose…</option>
                                                    @foreach($choices[$feature] as $name)
                                                        <option value="{{ $name }}">{{ $providers[$name]->label() }}</option>
                                                    @endforeach
                                                </select>
                                                @error("features.{$feature}.steps.{$index}.name")
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                                @if($driver && $driver->setupProblem() !== null)
                                                    <small class="text-muted d-block mt-1"><i class="fas fa-key mr-1"></i>No key. It is skipped until its key is in the .env file.</small>
                                                @endif
                                                @if(isset($quotas[$feature][$index]))
                                                    <small class="text-warning d-block mt-1">
                                                        <i class="fas fa-hourglass-half mr-1"></i>{{ $quotas[$feature][$index]['reason'] }}
                                                        <button type="button" class="btn btn-link btn-sm p-0 align-baseline"
                                                                wire:click="tryAgainNow('{{ $step['name'] }}', '{{ $step['model'] !== '' ? $step['model'] : $driver?->model() }}')">Try it again now</button>
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" list="{{ $listId }}" aria-label="Model"
                                                           wire:model="features.{{ $feature }}.steps.{{ $index }}.model"
                                                           placeholder="{{ $driver?->model() }}"
                                                           class="form-control @error("features.{$feature}.steps.{$index}.model") is-invalid @enderror">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-default" title="Load this provider's models"
                                                                wire:click="loadModels('{{ $step['name'] }}')" wire:loading.attr="disabled"
                                                                @if(! $driver || $driver->setupProblem() !== null) disabled @endif>
                                                            <i class="fas fa-list"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <datalist id="{{ $listId }}">
                                                    @foreach($modelLists[$step['name']] ?? [] as $modelId)
                                                        <option value="{{ $modelId }}">
                                                    @endforeach
                                                </datalist>
                                                @error("features.{$feature}.steps.{$index}.model")
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                                @if(isset($modelListErrors[$step['name']]))
                                                    <small class="text-danger d-block mt-1">{{ $modelListErrors[$step['name']] }}</small>
                                                @elseif(isset($modelLists[$step['name']]))
                                                    <small class="text-muted d-block mt-1">{{ count($modelLists[$step['name']]) }} models listed. Type to pick one.</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" step="0.0001" min="0" aria-label="Input price"
                                                           wire:model="features.{{ $feature }}.steps.{{ $index }}.input_price" placeholder="provider's"
                                                           class="form-control @error("features.{$feature}.steps.{$index}.input_price") is-invalid @enderror">
                                                </div>
                                                @error("features.{$feature}.steps.{$index}.input_price")
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" step="0.0001" min="0" aria-label="Output price"
                                                           wire:model="features.{{ $feature }}.steps.{{ $index }}.output_price" placeholder="provider's"
                                                           class="form-control @error("features.{$feature}.steps.{$index}.output_price") is-invalid @enderror">
                                                </div>
                                                @error("features.{$feature}.steps.{$index}.output_price")
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td class="align-middle">
                                                <button type="button" class="btn btn-default btn-sm" title="Remove"
                                                        wire:click="removeStep('{{ $feature }}', {{ $index }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(count($row['steps']) < $maxSteps)
                            <button type="button" class="btn btn-default btn-sm" wire:click="addStep('{{ $feature }}')">
                                <i class="fas fa-plus mr-1"></i> {{ $row['steps'] === [] ? 'Add a provider' : 'Add a backup' }}
                            </button>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
        <div class="card-footer d-flex align-items-center flex-wrap">
            <span class="small text-muted mr-auto">
                A function with its own providers tries only those, in this order, even ones turned off above.
                A blank model or price uses the provider's. Effort and server address always come from the provider.
            </span>
            <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                <i class="fas fa-save mr-1"></i> Save function models
            </button>
        </div>
    </div>
</div>
