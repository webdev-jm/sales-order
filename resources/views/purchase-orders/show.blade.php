@extends('adminlte::page')

@section('title')
    Purchase Orders
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Purchase Orders</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('purchase-order.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            Back
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-6">
            <h4>
                <strong>PO: </strong>{{$purchase_order->po_number}}
            </h4>
        </div>
    </div>
    
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            <b>Ship to Address: </b> <br>
            {{$purchase_order->ship_to_address}}
        </div>
        
        <div class="col-sm-4 invoice-col">
            <b>Po Value:</b> {{number_format($purchase_order->grand_total, 2)}}<br>
            <b>Approved Date:</b> {{$purchase_order->order_date}}<br>
            <b>Ship Date:</b> {{$purchase_order->ship_date}}<br>
            <b>Discount:</b> {{$logged_account->account->discount->description ?? ''}}<br>
            <b>Account:</b> [{{$logged_account->account->account_code}}] {{$logged_account->account->short_name}}
        </div>

        <div class="col-sm-4 invoice-col">
            <b>Control Number:</b> {{$purchase_order->status ?? ''}}<br>
            <b>Shipping Instruction:</b><br>
            <p>{{$purchase_order->shipping_instruction}}</p>
        </div>
    </div>

    <hr class="my-0">
    <table class="table table-sm">
        <thead>
            <tr class="text-center">
                <th class="text-left">SKU CODE</th>
                <th class="text-left">OTHER SKU CODE</th>
                <th class="text-left">SKU DESCRIPTION</th>
                <th class="text-left">UOM</th>
                <th class="text-right">TOTAL</th>
                <th class="text-right">QTY ORDERED</th>
                <th class="text-right">TOTAL LESS DISCOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase_order->details as $detail)
                <tr>
                    <td class="border-0 text-left">{{$detail->sku_code}}</td>
                    <td class="border-0 text-left">{{$detail->sku_code_other}}</td>
                    <td class="border-0 text-left">{{$detail->product_name}}</td>
                    <td class="border-0 text-left">{{$detail->unit_of_measure}}</td>
                    <td class="text-right border-0">{{number_format($detail->gross_amount, 2)}}</td>
                    <td class="text-right border-0">{{number_format($detail->quantity)}}</td>
                    <td class="text-right border-0">{{number_format($detail->net_amount, 2)}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">TOTAL GROSS AMOUNT</th>
                <th colspan="4" class="text-right">{{number_format($purchase_order->total_quantity)}}</th>
                <th class="text-right">{{number_format($purchase_order->total_sales, 2)}}</th>
            </tr>
            <tr>
                <th class="border-0" colspan="2">DISCOUNT</th>
                <th colspan="5" class="border-0 text-right">{{$logged_account->account->discount->description ?? ''}}</th>
            </tr>
            <tr>
                <th class="border-0" colspan="2">TOTAL NET AMOUNT</th>
                <th colspan="5" class="border-0 text-right">
                    {{number_format($purchase_order->grand_total, 2)}}
                </th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection

@section('js')
<script>
    $(function() {
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection