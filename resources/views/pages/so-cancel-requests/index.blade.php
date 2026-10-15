@extends('adminlte::page')

@section('title')
    SO Cancel Requests
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>SO Cancel Requests</h1>
    </div>
</div>
@endsection

@section('content')
    @can('so cancel request create')
        <livewire:sales-order-cancel-request.create />
    @endcan

    <livewire:sales-order-cancel-request.index />
@endsection
