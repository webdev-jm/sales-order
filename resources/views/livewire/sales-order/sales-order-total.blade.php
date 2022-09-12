<div>
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title pb-1">Order Summary</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-spinner fa-sm fa-spin"></i>
            </div>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label>TOTAL</label>
                        <input type="text" class="form-control bg-white" readonly wire:model="total">
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>DISCOUNT</label>
                        <input type="text" class="form-control bg-white" readonly value="{{$discount->description}}">
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>GRAND TOTAL</label>
                        <input type="text" class="form-control bg-white" readonly wire:model="grand_total">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title pb-1">Order Details</h3>
            <div class="card-tools" wire:loading>
                <i class="fa fa-spinner fa-sm fa-spin"></i>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm">
                <thead class="bg-secondary">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Unit</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
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
                            <td class="text-right align-middle p-1">{{$data['quantity']}}</td>
                            <td class="text-right align-middle p-1">{{$data['total']}}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
