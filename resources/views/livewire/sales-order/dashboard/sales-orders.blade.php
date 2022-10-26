<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sales Orders</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" class="form-control float-right" placeholder="Search" wire:model="search">
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Control Number</th>
                        <th>PO Number</th>
                        <th>Order Date</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Grand Total</th>
                        <th>Grand Total Less Discount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales_orders as $sales_order)
                    <tr>
                        <td>{{$sales_order->control_number}}</td>
                        <td>{{$sales_order->po_number}}</td>
                        <td>{{$sales_order->order_date}}</td>
                        <td>{{$sales_order->account_login->user->email}}</td>
                        <td>
                            {{$sales_order->account_login->user->firstname}}
                            {{$sales_order->account_login->user->lastname}}
                        </td>
                        <td class="text-right">
                            {{number_format($sales_order->total_sales, 2)}}
                        </td>
                        <td class="text-right">
                            {{number_format($sales_order->grand_total, 2)}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$sales_orders->links()}}
        </div>
    </div>
</div>
