@extends('adminlte::page')

@section('title')
    Sales Orders
@endsection

@section('css')
<script src="{{ asset('js/gsap.min.js') }}"></script>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>SALES ORDER MULTIPLE UPLOAD</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-order.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<livewire:sales-order.multiple.upload :logged_account="$logged_account" />

<div class="modal fade" id="summary-modal">
    <div class="modal-dialog modal-xl">
        <livewire:sales-order.multiple.summary/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '#btn-summary-modal', function(e) {
            e.preventDefault();
            $('#summary-modal').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
