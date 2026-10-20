@extends('adminlte::page')

@section('title')
    Sales Orders - Review Scanned PO
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/so-scan-review.css') }}">
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Orders / Review Scanned PO</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-order.create')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<livewire:accounts.active-account-banner/>

<livewire:sales-order.scan-review/>
@endsection

@section('js')
<script src="{{ asset('js/po-pdf-preview.js') }}"></script>
@endsection
