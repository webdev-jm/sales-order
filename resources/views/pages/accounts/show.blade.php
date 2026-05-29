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
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account details</h3>
            </div>
            <div class="card-body p-1">
                <ul class="list-group">
                    <li class="list-group-item py-2">
                        <strong>ACCOUNT CODE:</strong>
                        <span class="float-right">{{$account->account_code}}</span>
                    </li>
                    <li class="list-group-item py-2">
                        <strong>SHORT NAME:</strong>
                        <span class="float-right">{{$account->short_name}}</span>
                    </li>
                    <li class="list-group-item py-2">
                        <strong>ACCOUNT NAME:</strong>
                        <span class="float-right">{{$account->account_name}}</span>
                    </li>
                    <li class="list-group-item py-2">
                        <strong>COMPANY:</strong>
                        <span class="float-right">{{$account->company->name}}</span>
                    </li>
                    <li class="list-group-item py-2">
                        <strong>PRICE CODE:</strong>
                        <span class="float-right">{{$account->price_code}}</span>
                    </li>
                    <li class="list-group-item py-2">
                        <strong>DISCOUNT:</strong>
                        <span class="float-right">{{$account->discount->description ?? '-'}}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>

    <div class="col-lg-9">
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