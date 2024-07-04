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
        <a href="{{route('sales-order.index')}}" class="btn btn-primary">
            <i class="fa fa-list mr-1"></i>
            SALES ORDERS
        </a>
    </div>

    @if(!empty($cut_off))
    <div class="col-lg-12">
        <div class="alert alert-warning mb-0 mt-2" role="alert">
            <h4 class="alert-heading">NOTE:</h4>
            <p>
                {{$cut_off->message}}
            </p>
            <hr class="my-1">
            <p class="mb-0">SO CUT-OFF: from <b>{{date('Y-m-d H:i:s a', $cut_off->start_date)}}</b> to <b>{{date('Y-m-d H:i:s a', $cut_off->end_date)}}</b></p>
          </div>
    </div>
    @endif
</div>
@endsection

@section('content')
<livewire:purchase-order.index/>
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