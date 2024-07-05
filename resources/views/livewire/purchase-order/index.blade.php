<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Puchase Orders</h3>
            <div class="card-tools">
                <button class="btn btn-default btn-sm" disabled>
                    <i class="fa fa-filter mr-1"></i>
                    FILTER
                </button>
                <button class="btn btn-primary btn-sm" wire:click.prevent="createSO" {{empty($selected) ? 'disabled' : ''}} wire:loading.attr="disabled" wire:target="createSO">
                    <i class="fa fa-file-import mr-1"></i>
                    CREATE SO
                </button>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap table-sm">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" wire:change="checkAll" {{$checkedAll ? 'checked' : ''}} wire:loading.attr="disabled">
                        </th>
                        <th>PO Number</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Ship Date</th>
                        <th>Shipping Instruction</th>
                        <th>Ship To Name</th>
                        <th>Ship To Address</th>
                        <th class="text-right">Total Quantity</th>
                        <th class="text-right">Total Gross Amount</th>
                        <th class="text-right">Total Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase_orders as $order)
                        <tr>
                            <td>
                                <div wire:loading.remove wire:target="checkAll">
                                    <input type="checkbox" wire:change="check({{$order->id}})" {{!empty($selected[$order->id]) ? 'checked' : ''}}>
                                </div>
                                <div wire:loading wire:target="checkAll">
                                    <i class="fa fa-spinner fa-spin fa-xs"></i>
                                </div>
                            </td>
                            <td>
                                <a href="{{route('purchase-order.show', $order->id)}}">
                                    <u>{{$order->po_number}}</u>
                                </a>
                            </td>
                            <td>-</td>
                            <td>{{$order->order_date}}</td>
                            <td>{{$order->ship_date}}</td>
                            <td>{{$order->shipping_instruction}}</td>
                            <td>{{$order->ship_to_name}}</td>
                            <td>{{$order->ship_to_address}}</td>
                            <td class="text-right">{{number_format($order->total_quantity)}}</td>
                            <td class="text-right">{{number_format($order->total_sales, 2)}}</td>
                            <td class="text-right">{{number_format($order->grand_total, 2)}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$purchase_orders->links()}}
        </div>
    </div>
</div>
