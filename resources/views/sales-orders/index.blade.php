@extends('adminlte::page')

@section('title')
    Sales Orders
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Orders</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('sales order create')
        <a href="{{route('sales-order.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Sales Order</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['sales-order.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Sales Orders</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Control Number</th>
                    <th>PO Number</th>
                    <th>Order Date</th>
                    <th>Ship Date</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales_orders as $sales_order)
                <tr>
                    <td>{{$sales_order->control_number}}</td>
                    <td>{{$sales_order->po_number}}</td>
                    <td>{{$sales_order->order_date}}</td>
                    <td>{{$sales_order->ship_date}}</td>
                    <td>{{$sales_order->ship_to_name}}</td>
                    <td>
                        <span class="badge {{$sales_order->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$sales_order->status}}</span>
                    </td>
                    <td class="text-right">
                        @can('sales order edit')
                            <a href="{{route('sales-order.edit', $sales_order->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('sales order delete')
                            <a href="#" title="delete"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
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