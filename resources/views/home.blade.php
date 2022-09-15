@extends('adminlte::page')

@section('title')
    Home
@endsection

@section('css')
@endsection

@section('content_header')
    <h1>Home</h1>
@endsection

@section('content')
@if(!empty($logged_account))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Branches</h3>
    </div>
    <div class="card-body">
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
