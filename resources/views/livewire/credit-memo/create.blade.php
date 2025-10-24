<div>
    <div class="card">
        <div class="card-header">
        <h3 class="card-title">Search Invoice</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" class="form-control" placeholder="Enter Invoice Number" id="invoice_number" wire:model="invoice_number">
                        <small>{{ $errors->first('invoice_number') }}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="so_number">SO Number</label>
                        <input type="text" class="form-control" placeholder="Enter SO Number" id="so_number" wire:model="so_number">
                        <small>{{ $errors->first('so_number') }}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="po_number">PO Number</label>
                        <input type="text" class="form-control" placeholder="Enter PO Number" id="po_number" wire:model="po_number">
                        <small>{{ $errors->first('po_number') }}</small>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" class="form-control" placeholder="Enter Year" id="year" wire:model="year">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="month">Month</label>
                        <select class="form-control" id="month" wire:model="month">
                            <option value="">- select month -</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{$i}}">{{date('F', mktime(0, 0, 0, $i, 10))}}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="account_id">Account</label>
                        <select class="form-control" id="account_id" wire:model="account_id">
                            <option value="">- select account -</option>
                            @foreach($accounts as $account)
                                <option value="{{$account->id}}">{{$account->account_code}} {{$account->short_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer text-right">
            <button class="btn btn-primary btn-sm" wire:click.prevent="searchInvoice()" wire:loading.attr="disabled">
                <i class="fa fa-search mr-1"></i>
                Search Invoice
            </button>
            <button type="button" class="btn btn-info btn-sm" wire:click.prevent="showSummary">
                @if($show_summary)
                    HIDE SUMMARY
                @else
                    SHOW SUMMARY
                @endif
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RUD Details</h3>
            <div class="card-tools">
                @if($show_summary)
                    <button class="btn btn-secondary btn-sm" wire:click.prevent="saveRUD('draft')">
                        SAVE AS DRAFT
                    </button>
                    <button class="btn btn-success btn-sm" wire:click.prevent="saveRUD('submitted')">
                        SUBMIT
                    </button>
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

                    <div class="col-lg-12 table-responsive">
                        <table class="table table-bordered table-sm text-xs">
                            <thead>
                                <tr class="bg-secondary text-white">
                                    <th>Stock Code</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Uom</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cm_details as $key => $data)
                                    <tr>
                                        <td>{{ $data['product']['stock_code'] }}</td>
                                        <td>{{ $data['product']['description'] }}</td>
                                        <td class="p-0">
                                            <input type="number" class="form-control form-control-sm border-0">
                                        </td>
                                        <td class="p-0">
                                            <input type="text" class="form-control form-control-sm border-0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <div class="row">
                                                <div class="col-lg-5">
                                                    <table class="table table-sm table-bordered table-hover">
                                                        <tr>
                                                            <th>Warehouse:</th>
                                                            <td>{{$data['row_data']['warehouse']}}</td>
                                                            <th>Unit Cost:</th>
                                                            <td>{{$data['row_data']['unit_cost']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Bin:</th>
                                                            <td>{{$data['row_data']['bin']}}</td>
                                                            <th>Order UOM:</th>
                                                            <td>{{$data['row_data']['order_uom']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Order Quantity:</th>
                                                            <td>{{$data['row_data']['order_quantity']}}</td>
                                                            <th>Stock Quantity to Ship:</th>
                                                            <td>{{$data['row_data']['stock_quantity_to_ship']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Ship Quantity:</th>
                                                            <td>{{$data['row_data']['ship_quantity']}}</td>
                                                            <th>Stocking UOM:</th>
                                                            <td>{{$data['row_data']['stocking_uom']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Price:</th>
                                                            <td>{{$data['row_data']['price']}}</td>
                                                            <th>Price Uom:</th>
                                                            <td>{{$data['row_data']['price_uom']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Line Ship Date:</th>
                                                            <td colspan="3">{{$data['row_data']['line_ship_date']}}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-lg-7">
                                                    <table class="table table-sm table-bordered table-hover text-xs">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="5">LOT DETAILS</th>
                                                            </tr>
                                                            <tr>
                                                                <th>Lot</th>
                                                                <th>Bin</th>
                                                                <th>Quantity</th>
                                                                <th>Uom</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($data['data'] as $key => $bin_data)
                                                                <tr>
                                                                    <td>{{$bin_data['Lot']}}</td>
                                                                    <td>{{$bin_data['Bin']}}</td>
                                                                    <td class="p-0">
                                                                        <input type="number" class="form-control form-control-sm border-0" value="{{$bin_data['conversion'][$data['row_data']['order_uom']] ?? '-'}}">
                                                                    </td>
                                                                    <td class="p-0">
                                                                        <input type="text" class="form-control form-control-sm border-0" value="{{ $data['row_data']['order_uom'] }}">
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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
                            <button class="btn btn-sm btn-secondary" wire:click.prevent="clearDetail" wire:loading.attr="disabled">
                                <i class="fa fa-arrow-left mr-1"></i>
                                Back
                            </button>
                        </div>

                        <div class="col-lg-12 table-responsive">

                            <table class="table table-sm table-bordered text-xs">
                                <thead>
                                    <tr class="bg-secondary text-white">
                                        <th>Select</th>
                                        <th>Sales Order Line</th>
                                        <th>Stock Code</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                @foreach($detail_data as $key => $data)
                                    <livewire:credit-memo.cm-row :row_data="$data" wire:key="$key"/>
                                @endforeach
                            </table>
                        </div>
                    @else
                        <div class="col-lg-12 table-responsive">
                            @if(!empty($invoice_data))
                                <table class="table table-bordered table-hover table-sm text-xs">
                                    <thead>
                                        <tr class="bg-secondary text-white">
                                            <th>Invoice Number</th>
                                            <th>Sales Order</th>
                                            <th>Year</th>
                                            <th>Month</th>
                                            <th>Customer</th>
                                            <th>PO Number</th>
                                            <th>Order Date</th>
                                            <th>ReqShipDate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice_data as $key => $data)
                                            <tr wire:click.prevent="selectSalesOrder({{$key}})" style="cursor: pointer;">
                                                <td>{{ $data['InvoiceNumber'] }}</td>
                                                <td>{{ $data['SalesOrder'] }}</td>
                                                <td>{{ $data['TrnYear'] }}</td>
                                                <td>{{ $data['TrnMonth'] }}</td>
                                                <td>{{ $data['Customer'] }}</td>
                                                <td>{{ $data['CustomerPoNumber'] }}</td>
                                                <td>{{ $data['OrderDate'] }}</td>
                                                <td>{{ $data['ReqShipDate'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>No invoice data found.</p>
                            @endif
                        </div>
                    @endif

                </div>
            @endif

        </div>
    </div>

</div>
