<div>

    {{-- ================================================================= --}}
    {{-- STEP 1: Upload Form                                                --}}
    {{-- ================================================================= --}}
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
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   wire:model.debounce.300ms="accountSearch"
                                   class="form-control"
                                   placeholder="Filter accounts…">
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
            <button wire:click="upload"
                    wire:loading.attr="disabled"
                    wire:target="upload"
                    type="button"
                    class="btn btn-primary">
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

    {{-- ================================================================= --}}
    {{-- STEP 2: Review Table                                               --}}
    {{-- ================================================================= --}}
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

        {{-- Summary badges --}}
        <div class="card-body pb-0">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge badge-secondary badge-lg p-2">
                    Total: {{ $summary['total'] ?? 0 }}
                </span>
                <span class="badge badge-success badge-lg p-2">
                    <i class="fas fa-check mr-1"></i> OK: {{ $summary['ok'] ?? 0 }}
                </span>
                <span class="badge badge-warning badge-lg p-2 text-dark">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    SKU not found: {{ $summary['sku_not_found'] ?? 0 }}
                </span>
                <span class="badge badge-info badge-lg p-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    Address not found: {{ $summary['address_not_found'] ?? 0 }}
                </span>
                <span class="badge badge-danger badge-lg p-2">
                    <i class="fas fa-times mr-1"></i>
                    Both not found: {{ $summary['both_not_found'] ?? 0 }}
                </span>
            </div>

            {{-- Legend --}}
            <div class="small text-muted mb-2">
                <span class="badge badge-warning text-dark mr-1">&nbsp;</span> SKU not found &nbsp;
                <span class="badge badge-info mr-1">&nbsp;</span> Address not found &nbsp;
                <span class="badge badge-danger mr-1">&nbsp;</span> Both not found
            </div>
        </div>

        {{-- Table --}}
        <div class="card-body table-responsive p-0" style="max-height:70vh; overflow-y:auto;">
            <table class="table table-bordered table-sm table-hover text-nowrap" style="font-size:0.78rem;">
                <thead class="thead-dark" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th>#</th>
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
                            <td colspan="21" class="text-center text-muted py-3">No rows parsed.</td>
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

</div>
