@extends('adminlte::page')

@section('title')
    Sales Orders - Details
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Orders / Details</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-order.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sales Order Details</h3>
    </div>
    <div class="card-body">
    </div>
    <div class="card-footer">
    </div>
</div>

<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                {{$sales_order->control_number}}
                <small class="float-right">Order Date: {{$sales_order->order_date}}</small>
            </h4>
        </div>
    </div>
    
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            Ship to Address
            <address>
                <strong>{{$sales_order->ship_to_name}}</strong><br>
                {{$sales_order->ship_to_building}}<br>
                {{$sales_order->ship_to_street}}<br>
                {{$sales_order->ship_to_city}}<br>
                {{$sales_order->ship_to_postal}}
            </address>
        </div>
        
        <div class="col-sm-4 invoice-col">
            <b>PO Number: {{$sales_order->po_number}}</b><br>
            <br>
            <b>Ship Date:</b> {{$sales_order->ship_date}}<br>
            <b>Discount:</b> {{$sales_order->account_login->account->discount->description}}<br>
            <b>Account:</b> [{{$sales_order->account_login->account->account_code}}] {{$sales_order->account_login->account->short_name}}
        </div>
    </div>
    
    <div class="row">
        @foreach($parts as $part)
        <div class="col-12">
            <label>Part {{$part->part}}</label>
        </div>
        <div class="col-12 table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="align-middle">Stock Code</th>
                        <th class="align-middle">Description</th>
                        <th class="align-middle">Unit</th>
                        <th class="align-middle">Quantity</th>
                        <th class="align-middle">Total</th>
                        <th class="align-middle">Total less discount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num = 0;
                    @endphp
                    @foreach($sales_order->order_products()->where('part', $part->part)->get() as $order_product)
                    @php
                        $num++;
                    @endphp
                    <tr>
                        <td rowspan="{{$order_product->product_uoms->count() + 1}}" class="align-middle text    -center">{{$num}}</td>
                        <td rowspan="{{$order_product->product_uoms->count() + 1}}" class="align-middle">{{$order_product->product->stock_code}}</td>
                        <td rowspan="{{$order_product->product_uoms->count() + 1}}" class="align-middle">{{$order_product->product->description}} [{{$order_product->product->size}}]</td>
                    </tr>
                        @foreach($order_product->product_uoms as $uom)
                        <tr>
                            <td>{{$uom->uom}}</td>
                            <td>{{$uom->quantity}}</td>
                            <td>{{$uom->uom_total}}</td>
                            <td>{{$uom->uom_total_less_disc}}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    
</div>

@endsection

@section('js')
<script>

</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection