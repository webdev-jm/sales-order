<div>
    <button class="btn btn-secondary btn-sm mb-2" wire:click.prevent="showFilter">
        <i class="fa fa-filter mr-1"></i>
        FILTER
    </button>

    @if($showFilter)
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">FILTER</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-2">
                    <strong>STATUS:</strong>
                </div>
                <div class="col-lg-10 mb-2">
                    <select wire:model="filters.status">
                        <option value="">- SELECT -</option>
                        <option value="NULL">WITHOUT SO</option>
                        <option value="NOT NULL">WITH SO</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <strong>APPROVED DATE:</strong>
                </div>
                <div class="col-lg-10 mb-2">
                    <input type="date" wire:model="filters.approved_date_from">
                    <strong class="mx-2">TO</strong>
                    <input type="date" wire:model="filters.approved_date_to">
                </div>

                <div class="col-lg-2">
                    <strong>SHIP DATE:</strong>
                </div>
                <div class="col-lg-10">
                    <input type="date" wire:model="filters.ship_date_from">
                    <strong class="mx-2">TO</strong>
                    <input type="date" wire:model="filters.ship_date_to">
                </div>
            </div>
        </div>
        <div class="card-footer text-right p-1">
            <button class="btn btn-info btn-sm" wire:click.prevent="applyFilter">
                <i class="fa fa-check mr-1"></i>
                APPLY FILTER
            </button>
            <button class="btn btn-default btn-sm" wire:click.prevent="clearFilter">
                <i class="fa fa-eraser mr-1"></i>
                CLEAR FILTER
            </button>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">List of Puchase Orders</h3>
            <div class="card-tools">
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
                        <th>Approved Date</th>
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
                                    @if(empty($order->status))
                                        <input type="checkbox" wire:change="check({{$order->id}})" {{!empty($selected[$order->id]) ? 'checked' : ''}}>
                                    @else
                                        <input type="checkbox" disabled>
                                    @endif
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
                            <td>
                                @if(!empty($order->status))
                                    @php
                                        $so = \App\Models\SalesOrder::where('control_number', $order->status)->first();
                                    @endphp
                                    <a href="{{route('sales-order.show', $so->id)}}">
                                        {{$order->status}}
                                    </a>
                                @endif
                            </td>
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
        <div class="card-footer pb-0">
            {{$purchase_orders->links()}}
        </div>
    </div>
</div>
