<div class="card">
    <div class="card-header">
        <h3 class="card-title">RUD Details</h3>
        <div class="card-tools">
            @if($show_summary)
                <button class="btn btn-secondary btn-sm" wire:click.prevent="saveRUD('draft')">SAVE DRAFT</button>
                <button class="btn btn-success btn-sm" wire:click.prevent="saveRUD('submitted')">SUBMIT</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($show_summary)
            <div class="row">
                <div class="col-lg-6">
                    <ul class="list-group mb-2 text-sm">
                        <li class="list-group-item py-1">
                            <strong>ACCOUNT:</strong>
                            <span class="float-right">[{{ $cm_data['account']['account_code'] ?? '' }}] {{ $cm_data['account']['short_name'] ?? '' }}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>INVOICE:</strong>
                            <span class="float-right">{{ $cm_data['invoice_number'] ?? '' }}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SO NUMBER:</strong>
                            <span class="float-right">{{ $cm_data['so_number'] ?? '' }}</span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>PO NUMBER:</strong>
                            <span class="float-right">{{ $cm_data['po_number'] ?? '' }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 text-sm">
                    <ul class="list-group">
                        <li class="list-group-item py-1 pr-1 align-middle">
                            <strong>REASON:</strong>
                            <span class="float-right">
                                <select class="form-control{{ $errors->has('cm_reason_id') ? ' is-invalid' : ''}} form-control-sm" id="cm_reason_id" wire:model="cm_data.cm_reason_id">
                                    <option value="">- select reason -</option>
                                    @foreach($reasons as $reason)
                                        <option value="{{$reason->id}}">[{{$reason->reason_code}}] {{$reason->reason_description}}</option>
                                    @endforeach
                                </select>
                            </span>
                        </li>
                        <li class="list-group-item py-1 pr-1">
                            <strong>DATE:</strong>
                            <span class="float-right">
                                <input type="date" class="form-control form-control-sm" wire:model="cm_date">
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-12 table-responsive mt-3">
                    <table class="table table-bordered table-sm text-xs">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>Stock Code</th>
                                <th>Description</th>
                                <th>Lot / Bin Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cm_details as $stock => $detail)
                                <tr>
                                    <td class="align-middle">{{ $stock }}</td>
                                    <td class="align-middle">{{ $detail['product']['description'] ?? '' }}</td>
                                    <td class="p-0">
                                        <table class="table table-borderless table-sm mb-0">
                                            @foreach($detail['data'] as $bin_key => $bin)
                                                @php
                                                    // Extract current UOM key (e.g., 'EA') and Qty value
                                                    $currentUom = array_key_first($bin['conversion']);
                                                    $currentQty = $bin['conversion'][$currentUom];
                                                @endphp
                                                <tr>
                                                    <td class="align-middle border-bottom pl-2">
                                                        Lot: <strong>{{ $bin['Lot'] }}</strong> <br>
                                                        Bin: {{ $bin['Bin'] }}
                                                    </td>
                                                    <td class="align-middle border-bottom pr-2" width="180">
                                                        <div class="input-group input-group-sm">
                                                            <input type="number"
                                                                   class="form-control text-center"
                                                                   step="0.01"
                                                                   placeholder="Qty"
                                                                   value="{{ $currentQty }}"
                                                                   wire:change="updateQuantity('{{ $stock }}', '{{ $bin_key }}', $event.target.value)">

                                                            <input type="text"
                                                                   class="form-control text-center bg-light"
                                                                   placeholder="UOM"
                                                                   value="{{ $currentUom }}"
                                                                   style="max-width: 60px; font-weight: bold;"
                                                                   wire:change="updateUom('{{ $stock }}', '{{ $bin_key }}', $event.target.value)">
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="row">
                @if(!empty($detail_data))
                    <div class="col-lg-12 text-right mb-2">
                        <button class="btn btn-sm btn-secondary" wire:click.prevent="clearDetail">Back</button>
                    </div>
                    <div class="col-lg-12 table-responsive">
                        <table class="table table-sm table-bordered text-xs">
                            <thead class="bg-secondary text-white">
                                <tr><th>Select</th><th>Line</th><th>Stock Code</th><th>Description</th></tr>
                            </thead>
                            @foreach($detail_data as $key => $data)
                                <livewire:credit-memo.cm-row :row_data="$data" wire:key="$key"/>
                            @endforeach
                        </table>
                    </div>
                @elseif(!empty($invoice_data))
                    <div class="col-lg-12 table-responsive">
                        <table class="table table-bordered table-hover table-sm text-xs">
                            <thead><tr><th>Invoice</th><th>Sales Order</th><th>Customer</th><th>Date</th></tr></thead>
                            <tbody>
                                @foreach($invoice_data as $key => $data)
                                    <tr wire:click.prevent="selectSalesOrder({{$key}})" style="cursor: pointer;">
                                        <td>{{ $data['InvoiceNumber'] }}</td>
                                        <td>{{ $data['SalesOrder'] }}</td>
                                        <td>{{ $data['Customer'] }}</td>
                                        <td>{{ $data['OrderDate'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="p-3 text-muted">No data loaded. Use search above.</p>
                @endif
            </div>
        @endif
    </div>
</div>
