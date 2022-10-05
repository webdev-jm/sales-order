@extends('adminlte::page')

@section('title')
    Home
@endsection

@section('css')
<style>
    .h-90 {
        height: 89% !important;
    }
    .h-80 {
        height: 84% !important;
    }
</style>
@endsection

@section('content_header')
    <h1>Home</h1>
@endsection

@section('content')
@if(!empty($logged_account))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Activities</h3>
    </div>
    <div class="card-body">
        <a href="{{route('sales-order.create')}}" class="btn btn-primary">Booking Order</a>
    </div>
</div>
@elseif(!empty($logged_branch))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Activities</h3>
    </div>
    <div class="card-body">

        <livewire:activities.activities :logged_branch="$logged_branch"/>

    </div>
</div>
@else
    <livewire:accounts.account-login/>
@endif
@endsection

@section('js')
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
