<div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'upload' ? 'active' : '' }}"
               wire:click.prevent="setTab('upload')" href="#">
                <i class="fas fa-file-csv mr-1"></i> Upload Template
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'storemap' ? 'active' : '' }}"
               wire:click.prevent="setTab('storemap')" href="#">
                <i class="fas fa-map mr-1"></i> Store Map
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'productmap' ? 'active' : '' }}"
               wire:click.prevent="setTab('productmap')" href="#">
                <i class="fas fa-boxes mr-1"></i> Product Map
            </a>
        </li>
    </ul>

    {{-- ================================================================= --}}
    {{-- UPLOAD TAB                                                         --}}
    {{-- ================================================================= --}}
    @if($activeTab === 'upload')

    @if($step === 1)
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Upload Raw Puregold CSV</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                        <label>
                            Account <span class="text-danger">*</span>
                            <small class="text-muted ml-1">(hold Ctrl / Cmd to select multiple)</small>
                        </label>
                        <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.debounce.300ms="accountSearch"
                                   class="form-control" placeholder="Filter accounts…">
                        </div>
                        <select wire:model="account_ids" id="account_ids" multiple size="6"
                                class="form-control @error('account_ids') is-invalid @enderror"
                                style="height:auto;">
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    [{{ $account->account_code }}] {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_ids')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        @if(!empty($account_ids))
                            <small class="text-success mt-1 d-block">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ count($account_ids) }} account(s) selected
                            </small>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                        <label for="file">Raw CSV File <span class="text-danger">*</span></label>
                        <input type="file" wire:model="file" id="file" accept=".csv"
                               class="form-control @error('file') is-invalid @enderror">
                        @error('file')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <div wire:loading wire:target="file" class="text-muted small mt-1">
                            <i class="fas fa-spinner fa-spin"></i> Uploading…
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button wire:click="upload" wire:loading.attr="disabled" wire:target="upload"
                    type="button" class="btn btn-primary">
                <span wire:loading.remove wire:target="upload">
                    <i class="fas fa-cogs mr-1"></i> Parse File
                </span>
                <span wire:loading wire:target="upload">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Parsing…
                </span>
            </button>
        </div>
    </div>
    @endif

    @if($step === 2)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Parsed Template — Review</h3>
            <div class="card-tools">
                <button wire:click="resetUpload" type="button" class="btn btn-default btn-sm mr-1">
                    <i class="fas fa-arrow-left mr-1"></i> Upload Another
                </button>
                <button wire:click="export" type="button" class="btn btn-secondary btn-sm mr-1">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </button>
                <button wire:click="exportExcel" type="button" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </button>
                <button wire:click="exportTemplate" type="button" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-upload mr-1"></i> Export Upload Template
                </button>
            </div>
        </div>
        <div class="card-body pb-0">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge badge-secondary badge-lg p-2">Total: {{ $summary['total'] ?? 0 }}</span>
                <span class="badge badge-success badge-lg p-2">
                    <i class="fas fa-check mr-1"></i> OK: {{ $summary['ok'] ?? 0 }}
                </span>
                <span class="badge badge-warning badge-lg p-2 text-dark">
                    <i class="fas fa-exclamation-triangle mr-1"></i> SKU not found: {{ $summary['sku_not_found'] ?? 0 }}
                </span>
                <span class="badge badge-info badge-lg p-2">
                    <i class="fas fa-map-marker-alt mr-1"></i> Address not found: {{ $summary['address_not_found'] ?? 0 }}
                </span>
                <span class="badge badge-danger badge-lg p-2">
                    <i class="fas fa-times mr-1"></i> Both not found: {{ $summary['both_not_found'] ?? 0 }}
                </span>
            </div>
            <div class="small text-muted mb-2">
                <span class="badge badge-warning text-dark mr-1">&nbsp;</span> SKU not found &nbsp;
                <span class="badge badge-info mr-1">&nbsp;</span> Address not found &nbsp;
                <span class="badge badge-danger mr-1">&nbsp;</span> Both not found
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="max-height:70vh; overflow-y:auto;">
            <table class="table table-bordered table-sm table-hover text-nowrap" style="font-size:0.78rem;">
                <thead class="thead-dark" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th>#</th>
                        <th>Account</th>
                        <th>PO Date</th>
                        <th>PO Number</th>
                        <th>Store Name</th>
                        <th>Store Code</th>
                        <th>Delivery Date</th>
                        <th>Cancel Date</th>
                        <th>Depot</th>
                        <th>Del Loc</th>
                        <th>PO Remarks</th>
                        <th>Raw SKU</th>
                        <th>SKU Code</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>List Price</th>
                        <th>Amount</th>
                        <th>Internal SKU</th>
                        <th>Product Name</th>
                        <th>Ship Name</th>
                        <th>Ship Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                        @php
                            $rowClass = match($row['lookup_status']) {
                                'sku_not_found'     => 'table-warning',
                                'address_not_found' => 'table-info',
                                'both_not_found'    => 'table-danger',
                                default             => '',
                            };
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if(!empty($row['account_code']))
                                    [{{ $row['account_code'] }}] {{ $row['account_name'] }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $row['po_date'] }}</td>
                            <td>{{ $row['po_number'] }}</td>
                            <td>{{ $row['store_name'] }}</td>
                            <td>{{ $row['store_code'] }}</td>
                            <td>{{ $row['delivery_date'] }}</td>
                            <td>{{ $row['cancellation_date'] }}</td>
                            <td>{{ $row['depot'] }}</td>
                            <td>{{ $row['del_loc'] }}</td>
                            <td>{{ $row['po_remarks'] }}</td>
                            <td>{{ $row['raw_sku'] }}</td>
                            <td>{{ $row['sku_code'] }}</td>
                            <td>{{ $row['description'] }}</td>
                            <td>{{ $row['qty'] }}</td>
                            <td>{{ $row['list_price'] }}</td>
                            <td>{{ $row['amount'] }}</td>
                            <td>{{ $row['internal_sku'] ?? '—' }}</td>
                            <td>{{ $row['product_name'] ?? '—' }}</td>
                            <td>{{ $row['shipping_name'] ?? '—' }}</td>
                            <td>{{ $row['shipping_address'] ?? '—' }}</td>
                            <td>
                                @if($row['lookup_status'] === 'ok')
                                    <span class="badge badge-success">ok</span>
                                @elseif($row['lookup_status'] === 'sku_not_found')
                                    <span class="badge badge-warning text-dark">sku</span>
                                @elseif($row['lookup_status'] === 'address_not_found')
                                    <span class="badge badge-info">addr</span>
                                @else
                                    <span class="badge badge-danger">both</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="22" class="text-center text-muted py-3">No rows parsed.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <button wire:click="export" type="button" class="btn btn-secondary btn-sm mr-1">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </button>
            <button wire:click="exportExcel" type="button" class="btn btn-success btn-sm mr-1">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
            <button wire:click="exportTemplate" type="button" class="btn btn-primary btn-sm">
                <i class="fas fa-file-upload mr-1"></i> Export Upload Template
            </button>
        </div>
    </div>
    @endif

    @endif {{-- end upload tab --}}

    {{-- ================================================================= --}}
    {{-- STORE MAP TAB                                                      --}}
    {{-- ================================================================= --}}
    @if($activeTab === 'storemap')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Puregold Store Map</h3>
        </div>

        {{-- Search + Add form --}}
        <div class="card-body border-bottom">
            <div class="form-row align-items-end mb-3">
                <div class="col-auto">
                    <label class="small font-weight-bold">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" wire:model.debounce.300ms="storeMapSearch"
                               class="form-control" placeholder="Store code or BEVI code…" style="width:220px;">
                    </div>
                </div>
            </div>
            <div class="form-row align-items-end">
                <div class="col-auto">
                    <label class="small font-weight-bold">Store Code</label>
                    <input type="text" wire:model.defer="newStoreCode"
                           class="form-control form-control-sm @error('newStoreCode') is-invalid @enderror"
                           placeholder="e.g. 1061" style="width:140px;">
                    @error('newStoreCode')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    <label class="small font-weight-bold">BEVI Code</label>
                    <input type="text" wire:model.defer="newBeviCode"
                           class="form-control form-control-sm @error('newBeviCode') is-invalid @enderror"
                           placeholder="e.g. 01061" style="width:140px;">
                    @error('newBeviCode')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    <button wire:click="addStoreMap" type="button" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-body p-0">
            <table class="table table-bordered table-sm table-hover mb-0" style="font-size:0.85rem;">
                <thead class="thead-light">
                    <tr>
                        <th style="width:160px;">Store Code</th>
                        <th style="width:160px;">BEVI Code</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($storeMaps as $map)
                        <tr wire:key="storemap-{{ $map->id }}">
                            @if($editingId === $map->id)
                                <td>
                                    <input type="text" wire:model.defer="editStoreCode"
                                           class="form-control form-control-sm @error('editStoreCode') is-invalid @enderror">
                                    @error('editStoreCode')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text" wire:model.defer="editBeviCode"
                                           class="form-control form-control-sm @error('editBeviCode') is-invalid @enderror">
                                    @error('editBeviCode')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td class="text-nowrap">
                                    <button wire:click="saveEdit" type="button" class="btn btn-primary btn-xs mr-1">
                                        <i class="fas fa-check"></i> Save
                                    </button>
                                    <button wire:click="cancelEdit" type="button" class="btn btn-default btn-xs">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </td>
                            @else
                                <td>{{ $map->store_code }}</td>
                                <td>{{ $map->bevi_code }}</td>
                                <td class="text-nowrap">
                                    <button wire:click="startEdit({{ $map->id }})" type="button" class="btn btn-warning btn-xs mr-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button wire:click="deleteStoreMap({{ $map->id }})"
                                            wire:confirm="Delete this store mapping?"
                                            type="button" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No mappings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">{{ $storeMaps->total() }} mapping(s)</span>
            {{ $storeMaps->links() }}
        </div>
    </div>
    @endif

    {{-- ================================================================= --}}
    {{-- PRODUCT MAP TAB                                                    --}}
    {{-- ================================================================= --}}
    @if($activeTab === 'productmap')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product Map (Account References)</h3>
        </div>

        {{-- Filters + Add form --}}
        <div class="card-body border-bottom">

            {{-- Filter bar --}}
            <div class="form-row align-items-end mb-3">
                <div class="col-auto">
                    <label class="small font-weight-bold">Filter by Account</label>
                    <select wire:model="productMapAccountId"
                            class="form-control form-control-sm" style="width:220px;">
                        <option value="">— All accounts —</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">[{{ $acc->account_code }}] {{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="small font-weight-bold">Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" wire:model.debounce.300ms="productMapSearch"
                               class="form-control" placeholder="Ref code, SKU, or description…" style="width:240px;">
                    </div>
                </div>
            </div>

            {{-- Add form --}}
            <div class="form-row align-items-end">
                <div class="col-auto">
                    <label class="small font-weight-bold">Account <span class="text-danger">*</span></label>
                    <select wire:model="newPmAccountId"
                            class="form-control form-control-sm @error('newPmAccountId') is-invalid @enderror"
                            style="width:220px;">
                        <option value="">— select —</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">[{{ $acc->account_code }}] {{ $acc->account_name }}</option>
                        @endforeach
                    </select>
                    @error('newPmAccountId')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    <label class="small font-weight-bold">Account Ref <span class="text-danger">*</span></label>
                    <input type="text" wire:model.defer="newPmAccountRef"
                           class="form-control form-control-sm @error('newPmAccountRef') is-invalid @enderror"
                           placeholder="Customer SKU code" style="width:160px;">
                    @error('newPmAccountRef')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    <label class="small font-weight-bold">Product <span class="text-danger">*</span></label>
                    <input type="text" wire:model.debounce.300ms="newPmProductSearch"
                           class="form-control form-control-sm mb-1"
                           placeholder="Search product…" style="width:200px;">
                    <select wire:model="newPmProductId"
                            class="form-control form-control-sm @error('newPmProductId') is-invalid @enderror"
                            style="width:200px;" size="3">
                        <option value="">— select product —</option>
                        @foreach($pmProducts as $prod)
                            <option value="{{ $prod->id }}">[{{ $prod->stock_code }}] {{ $prod->description }} {{ $prod->size }}</option>
                        @endforeach
                    </select>
                    @error('newPmProductId')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-auto">
                    <label class="small font-weight-bold">Description</label>
                    <input type="text" wire:model.defer="newPmDescription"
                           class="form-control form-control-sm"
                           placeholder="Optional" style="width:180px;">
                </div>
                <div class="col-auto" style="padding-top:1.5rem;">
                    <button wire:click="addProductMap" type="button" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-body p-0">
            <table class="table table-bordered table-sm table-hover mb-0 text-nowrap" style="font-size:0.82rem;">
                <thead class="thead-light">
                    <tr>
                        <th>Account</th>
                        <th>Account Ref</th>
                        <th>Internal SKU</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th style="width:130px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productMaps as $ref)
                        <tr wire:key="pm-{{ $ref->id }}">
                            @if($pmEditingId === $ref->id)
                                <td class="text-muted small">
                                    [{{ $ref->account->account_code ?? '?' }}] {{ $ref->account->account_name ?? '' }}
                                </td>
                                <td>
                                    <input type="text" wire:model.defer="pmEditAccountRef"
                                           class="form-control form-control-sm @error('pmEditAccountRef') is-invalid @enderror"
                                           style="width:130px;">
                                    @error('pmEditAccountRef')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td colspan="2">
                                    <input type="text" wire:model.debounce.300ms="pmEditProductSearch"
                                           class="form-control form-control-sm mb-1"
                                           placeholder="Search product…" style="width:200px;">
                                    <select wire:model="pmEditProductId"
                                            class="form-control form-control-sm @error('pmEditProductId') is-invalid @enderror"
                                            style="width:300px;" size="3">
                                        <option value="">— select product —</option>
                                        @foreach($pmEditProducts as $prod)
                                            <option value="{{ $prod->id }}">[{{ $prod->stock_code }}] {{ $prod->description }} {{ $prod->size }}</option>
                                        @endforeach
                                    </select>
                                    @error('pmEditProductId')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text" wire:model.defer="pmEditDescription"
                                           class="form-control form-control-sm"
                                           style="width:160px;" placeholder="Description">
                                </td>
                                <td class="text-nowrap">
                                    <button wire:click="saveEditPm" type="button" class="btn btn-primary btn-xs mr-1">
                                        <i class="fas fa-check"></i> Save
                                    </button>
                                    <button wire:click="cancelEditPm" type="button" class="btn btn-default btn-xs">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </td>
                            @else
                                <td>[{{ $ref->account->account_code ?? '?' }}] {{ $ref->account->account_name ?? '—' }}</td>
                                <td>{{ $ref->account_reference }}</td>
                                <td>{{ $ref->product->stock_code ?? '—' }}</td>
                                <td>{{ $ref->product ? trim($ref->product->description . ' ' . $ref->product->size) : '—' }}</td>
                                <td>{{ $ref->description ?? '' }}</td>
                                <td class="text-nowrap">
                                    <button wire:click="startEditPm({{ $ref->id }})" type="button" class="btn btn-warning btn-xs mr-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button wire:click="deleteProductMap({{ $ref->id }})"
                                            wire:confirm="Delete this product mapping?"
                                            type="button" class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No product mappings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">{{ $productMaps->total() }} mapping(s)</span>
            {{ $productMaps->links() }}
        </div>
    </div>
    @endif

</div>
