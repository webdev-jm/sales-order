@extends('adminlte::page')

@section('title')
    Sales Orders List
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Orders List</h1>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['sales-order.list'], 'id' => 'search_form']) !!}
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
                    <th>Account</th>
                    <th>Order Date</th>
                    <th>Ship Date</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Reference</th>
                    <th>Created By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales_orders as $sales_order)
                <tr>
                    <td>{{$sales_order->control_number}}</td>
                    <td>{{$sales_order->po_number}}</td>
                    <td>
                        [{{$sales_order->account_login->account->account_code}}] {{$sales_order->account_login->account->short_name}}
                    </td>
                    <td>{{$sales_order->order_date}}</td>
                    <td>{{$sales_order->ship_date}}</td>
                    <td>{{$sales_order->ship_to_name}}</td>
                    <td>
                        @if(isset($sales_order->upload_status))
                        <span class="badge {{$sales_order->upload_status == 1 ? 'badge-info' : 'badge-warning'}}">{{$sales_order->upload_status == 1 ? 'Uploaded' : 'Upload Error'}}</span>
                        @else
                        <span class="badge {{$sales_order->status == 'draft' ? 'badge-secondary' : 'badge-success'}}">{{$sales_order->status}}</span>
                        @endif
                    </td>
                    <td>
                        {{$sales_order->reference}}
                    </td>
                    <td>{{$sales_order->account_login->user->firstname}} {{$sales_order->account_login->user->lastname}}</td>
                    <td class="text-right">
                        @can('sales order change status')
                        <livewire:sales-order.change-status :sales_order_id="$sales_order->id"/>
                        @endcan
                        <a href="{{route('sales-order.show', $sales_order->id)}}" title="view">
                            <i class="fa fa-eye text-primary mx-1"></i>
                        </a>
                        @can('sales order delete')
                            <a href="#" title="delete">
                                <i class="fas fa-trash-alt text-danger mx-1"></i>
                            </a>
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