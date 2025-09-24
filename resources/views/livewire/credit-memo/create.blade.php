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
                        <label for="year">Year</label>
                        <input type="number" class="form-control" placeholder="Enter Year" id="year" wire:model="year">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="month">Month</label>
                        <select class="form-control" id="month" wire:model="month">
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
            <button class="btn btn-primary" wire:click.prevent="searchInvoice()" wire:loading.attr="disabled">Search Invoice</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Credit Memo Details</h3>
        </div>
        <div class="card-body">

            <div class="row">

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="cm_reason_id">Reason</label>
                        <select class="form-control select" id="cm_reason_id" wire:model="cm_reason_id">
                            <option value="">- select reason -</option>
                            @foreach($reasons as $reason)
                                <option value="{{$reason->id}}">[{{$reason->reason_code}}] {{$reason->reason_description}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-12 table-responsive">
                    <table class="table table-sm table-bordered text-xs">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle">Invoice Number</th>
                                <th class="align-middle">Sales Order</th>
                                <th class="align-middle">Year</th>
                                <th class="align-middle">Month</th>
                                <th class="align-middle">Customer</th>
                                <th class="align-middle">Customer Po Number</th>
                                <th class="align-middle">Order Date</th>
                                <th class="align-middle">Req Ship Date</th>
                                <th class="align-middle">Ent Invoice Date</th>
                                <th class="align-middle">Stock Code</th>
                                <th class="align-middle">Stock Description</th>
                                <th class="align-middle">Warehouse</th>
                                <th class="align-middle">Bin</th>
                                <th class="align-middle">Order Qty</th>
                                <th class="align-middle">Order Uom</th>
                                <th class="align-middle">Stock Qty To Ship</th>
                                <th class="align-middle">Stocking Uom</th>
                                <th class="align-middle">Conv Fact Ord Uom</th>
                                <th class="align-middle">Mul Div C</th>
                                <th class="align-middle">Price</th>
                                <th class="align-middle">Price Uom</th>
                                <th class="align-middle">Lot</th>
                                <th class="align-middle">Lot Stock Qty To Ship</th>
                                <th class="align-middle">Lot Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($invoice_data))
                                @foreach($invoice_data as $data)
                                    <tr>
                                        <td>{{$data['InvoiceNumber']}}</td>
                                        <td>{{$data['SalesOrder']}}</td>
                                        <td>{{$data['TrnYear']}}</td>
                                        <td>{{$data['TrnMonth']}}</td>
                                        <td>{{$data['Customer']}}</td>
                                        <td>{{$data['CustomerPoNumber']}}</td>
                                        <td>{{$data['OrderDate']}}</td>
                                        <td>{{$data['ReqShipDate']}}</td>
                                        <td>{{$data['EntInvoiceDate']}}</td>
                                        <td>{{$data['StockCode']}}</td>
                                        <td>{{$data['StockDescription']}}</td>
                                        <td>{{$data['Warehouse']}}</td>
                                        <td>{{$data['Bin']}}</td>
                                        <td>{{$data['OrderQty']}}</td>
                                        <td>{{$data['OrderUom']}}</td>
                                        <td>{{$data['StockQtyToShip']}}</td>
                                        <td>{{$data['StockingUom']}}</td>
                                        <td>{{$data['ConvFactOrdUom']}}</td>
                                        <td>{{$data['MulDivC']}}</td>
                                        <td>{{$data['Price']}}</td>
                                        <td>{{$data['PriceUom']}}</td>
                                        <td>{{$data['Lot']}}</td>
                                        <td>{{$data['LotStockQtyToShip']}}</td>
                                        <td>{{$data['LotExpiryDate']}}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
