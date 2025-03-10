@extends('adminlte::page')

@section('title')
    Invoices
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Invoices</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')
    <livewire:invoice.invoice-data :logged_account="$logged_account"/>
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