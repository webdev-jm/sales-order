@extends('adminlte::page')

@section('title')
    Sales Orders - Details
@endsection

@section('css')
<style>
    .bg-thead {
        background-color: #b1b1b1;
        color: rgb(255, 255, 255);
    }
</style>
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

        <div class="col-sm-4 invoice-col">
            <b>Shipping Instruction:</b><br>
            <p>
                {{$sales_order->shipping_instruction}}
            </p>
        </div>
    </div>
    
    <div class="row">
        @foreach($parts as $part)
        <div class="col-12">
            <label>Part {{$part->part}}</label>
        </div>
        <div class="col-12 table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="bg-thead">
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
                        $quantity_total = 0;
                        $sales_total = 0;
                        $sales_total_less_disc = 0;
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
                        @php
                            $quantity_total += $uom->quantity;
                            $sales_total += $uom->uom_total;
                            $sales_total_less_disc +=$uom->uom_total_less_disc;
                        @endphp
                        <tr>
                            <td>{{$uom->uom}}</td>
                            <td>{{$uom->quantity}}</td>
                            <td>{{number_format($uom->uom_total, 2)}}</td>
                            <td>{{number_format($uom->uom_total_less_disc, 2)}}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">TOTAL</th>
                        <th>{{number_format($quantity_total)}}</th>
                        <th>{{number_format($sales_total, 2)}}</th>
                        <th>{{number_format($sales_total_less_disc, 2)}}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-6">
            <p class="lead">Order Summary</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Total:</th>
                        <td>{{number_format($sales_order->total_sales, 2)}}</td>
                    </tr>
                    <tr>
                        <th>Discount</th>
                        <td>{{$sales_order->account_login->account->discount->description}}</td>
                    </tr>
                    <tr>
                        <th>Total Less Discount</th>
                        <td>{{number_format($sales_order->grand_total, 2)}}</td>
                    </tr>
                </table>
            </div>
        </div>
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