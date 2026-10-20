<div>
    @if($unmatched > 0 || $suggestions)
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-magic mr-1"></i> AI mapping help</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-info" wire:click="suggest" wire:loading.attr="disabled" wire:target="suggest">
                        <span wire:loading.remove wire:target="suggest"><i class="fas fa-search mr-1"></i> Suggest matches for unmatched lines</span>
                        <span wire:loading wire:target="suggest"><i class="fas fa-spinner fa-spin mr-1"></i> Looking for matches…</span>
                    </button>
                    @if($suggestions)
                        <button type="button" class="btn btn-tool" wire:click="dismiss" title="Close"><i class="fas fa-times"></i></button>
                    @endif
                </div>
            </div>

            @if($error !== '' || $notice !== '' || $suggestions)
                <div class="card-body p-0">
                    @if($error !== '')
                        <p class="text-warning m-3"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $error }}</p>
                    @endif
                    @if($notice !== '')
                        <p class="text-muted m-3"><i class="fas fa-info-circle mr-1"></i>{{ $notice }}</p>
                    @endif

                    @if($suggestions)
                        <p class="text-muted small mx-3 mt-3 mb-2">Check each suggestion before adding it. Adding saves the mapping for the account, so later uploads match too.</p>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Upload line</th>
                                        <th>Suggested match</th>
                                        <th>Confidence</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suggestions['products'] as $index => $suggestion)
                                        <tr wire:key="product-suggestion-{{ $index }}">
                                            <td class="text-nowrap">{{ $suggestion['account'] }}</td>
                                            <td><span class="badge badge-warning mr-1">SKU</span><strong>{{ $suggestion['sku'] }}</strong> {{ $suggestion['description'] }}</td>
                                            <td><strong>{{ $suggestion['code'] }}</strong> {{ $suggestion['name'] }}<br><small class="text-muted">{{ $suggestion['reason'] }}</small></td>
                                            <td>@include('livewire.ai.partials.confidence', ['confidence' => $suggestion['confidence']])</td>
                                            <td class="text-right text-nowrap">
                                                @if(!empty($suggestion['added']))
                                                    <span class="text-success"><i class="fas fa-check mr-1"></i>Added</span>
                                                @else
                                                    <button type="button" class="btn btn-xs btn-success" wire:click="addProduct({{ $index }})" wire:loading.attr="disabled">
                                                        <i class="fas fa-plus mr-1"></i> Add mapping
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach($suggestions['stores'] as $index => $suggestion)
                                        <tr wire:key="store-suggestion-{{ $index }}">
                                            <td class="text-nowrap">{{ $suggestion['account'] }}</td>
                                            <td><span class="badge badge-info mr-1">Store</span><strong>{{ $suggestion['store_code'] }}</strong> {{ $suggestion['store_name'] }}</td>
                                            <td><strong>{{ $suggestion['code'] }}</strong> {{ $suggestion['name'] }}<br><small class="text-muted">{{ $suggestion['reason'] }}</small></td>
                                            <td>@include('livewire.ai.partials.confidence', ['confidence' => $suggestion['confidence']])</td>
                                            <td class="text-right text-nowrap">
                                                @if(!empty($suggestion['added']))
                                                    <span class="text-success"><i class="fas fa-check mr-1"></i>Added</span>
                                                @else
                                                    <button type="button" class="btn btn-xs btn-success" wire:click="addStore({{ $index }})" wire:loading.attr="disabled">
                                                        <i class="fas fa-plus mr-1"></i> Add mapping
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
