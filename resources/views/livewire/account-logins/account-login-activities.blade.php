<div>
    <div class="modal-header">
        <h4 class="modal-title">Activities</h4>
        <div class="card-tools" wire:loading>
            <i class="fa fa-circle-notch fa-spin"></i>
        </div>
    </div>
    <div class="modal-body">
        @isset($account_login)
            <label>Login Details:</label>
            <p>
                {{$account_login->activities}}
            </p>
            @if(file_exists(public_path('uploads/account-login/'.$account_login->user_id.'/'.$account_login->id.'/small.jpg')))
            <label>Picture</label>
            <div class="mb-3">
                <a href="{{ asset('uploads/account-login/'.$account_login->user_id.'/'.$account_login->id).'/large.jpg' }}" data-toggle="lightbox" data-title="Preview">
                    <img src="{{asset('uploads/account-login/'.$account_login->user_id.'/'.$account_login->id.'/small.jpg')}}" alt="picture">
                </a>
            </div>
            @endif
        @endisset

        @isset($sales_orders)
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Control Number</th>
                        <th>PO Number</th>
                        <th>Order Date</th>
                        <th>Ship Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales_orders as $sales_order)
                    <tr>
                        <td>
                            @if(auth()->user()->can('sales order access'))
                            <a href="{{route('sales-order.show', $sales_order->id)}}">
                                {{$sales_order->control_number}}
                            </a>
                            @else
                            {{$sales_order->control_number}}
                            @endif
                        </td>
                        <td>
                            {{$sales_order->po_number}}
                        </td>
                        <td>
                            {{$sales_order->order_date}}
                        </td>
                        <td>
                            {{$sales_order->ship_date}}
                        </td>
                        <td>
                            <span class="badge {{$sales_order->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$sales_order->status}}</span>
                            
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endisset

    </div>
    <div class="modal-footer text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</div>
