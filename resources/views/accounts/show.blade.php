@extends('adminlte::page')

@section('title')
    Account
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Account</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account details</h3>
            </div>
            <div class="card-body">
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <livewire:accounts.templates.template-list :account="$account"/>
    </div>
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