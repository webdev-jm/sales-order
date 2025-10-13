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
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="invoice_number">SO Number</label>
                        <input type="text" class="form-control" placeholder="Enter SO Number" id="" wire:model="so_number">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="invoice_number">PO Number</label>
                        <input type="text" class="form-control" placeholder="Enter PO Number" id="invoice_number" wire:model="po_number">
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
            <button class="btn btn-primary" wire:click.prevent="searchInvoice()" wire:loading.attr="disabled">
                <i class="fa fa-search mr-1"></i>
                Search Invoice
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">RUD Details</h3>
        </div>
        <div class="card-body">

            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="cm_reason_id">Reason</label>
                        <select class="form-control" id="cm_reason_id" wire:model="cm_reason_id">
                            <option value="">- select reason -</option>
                            @foreach($reasons as $reason)
                                <option value="{{$reason->id}}">[{{$reason->reason_code}}] {{$reason->reason_description}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

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
                                <tr>
                                    <th>Select</th>
                                    <th>Sales Order Line</th>
                                    <th>Stock Code</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Uom</th>
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
                                        <tr wire:click.prevent="selectSalesOrder({{$key}})">
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

        </div>
    </div>
</div>
