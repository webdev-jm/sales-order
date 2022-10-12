<div>
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title pb-1">ORDER SUMMARY</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-sm fa-circle-notch fast-spin"></i>
            </div>
        </div>
        <div class="card-body">
            
            <div class="row">

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>TOTAL</label>
                        <input type="text" class="form-control bg-white text-right" readonly wire:model="total">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>ORDER DISCOUNT</label>
                        <input type="text" class="form-control bg-white" readonly value="{{$discount->description ?? '0'}}">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>TOTAL NET DISCOUNT</label>
                        <input type="text" class="form-control bg-white text-right" readonly wire:model="grand_total">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>PO value</label>
                        <input type="number" class="form-control bg-white{{isset($po_message) && $po_message != '' ? ' is-invalid' : ''}}" wire:model.lazy="po_value" wire:change="change_po_value">
                        @if(isset($po_message) && $po_message != '')
                            <p class="text-danger"><b>NOTE:</b> {{$po_message}}</p>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Total Quantity</label>
                        <input type="text" class="form-control bg-white text-right" readonly value="{{number_format($orders['total_quantity'] ?? 0)}}">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title pb-1">ORDER DETAILS</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-sm fa-circle-notch fast-spin"></i>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm">
                <thead class="bg-secondary">
                    <tr>
                        <th></th>
                        <th class="align-middle">Product</th>
                        <th class="text-center align-middle">Unit</th>
                        <th class="text-center align-middle">Quantity</th>
                        <th class="text-center align-middle">Total</th>
                        <th class="text-center align-middle">Line Discount</th>
                        <th class="text-center align-middle">Total less discount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num = 0;
                    @endphp
                    @if(!empty($orders['items']))
                        @foreach($orders['items'] as $order)
                            @php
                                $num++;
                            @endphp
                            <tr>
                                <td class="align-middle text-center p-0 font-weight-bold" rowspan="{{count($order['data']) + 1}}">{{$num}}.</td>
                                <td class="align-middle p-2" rowspan="{{count($order['data']) + 1}}">
                                    <span class="font-weight-bold">{{$order['stock_code']}}</span>
                                    <br>
                                    <span>{{$order['description']}}</span>
                                    <span class="text-muted">[{{$order['size']}}]</span>
                                </td>
                            </tr>
                            @foreach($order['data'] as $uom => $data)
                            <tr>
                                <td class="text-center align-middle p-0">{{$uom}}</td>
                                <td class="text-right align-middle p-1">{{number_format($data['quantity'])}}</td>
                                <td class="text-right align-middle p-1">{{number_format($data['total'], 2)}}</td>
                                <td class="text-right align-middle p-1">{{$data['discount']}}</td>
                                <td class="text-right align-middle p-1">{{number_format($data['discounted'], 2)}}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer">
        </div>
    </div>
</div>
