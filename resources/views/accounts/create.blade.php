@extends('adminlte::page')

@section('title')
    Accounts - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Accounts / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('account.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Add Account</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'post', 'route' => ['account.store'], 'id' => 'add_account']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('company_id', 'Company') !!}
                    {!! Form::select('company_id', $companies, null, ['class' => 'form-control'.($errors->has('company_id') ? ' is-invalid' : ''), 'form' => 'add_account']) !!}
                    <p class="text-danger">{{$errors->first('company_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_code', 'Account Code') !!}
                    {!! Form::text('account_code', '', ['class' => 'form-control'.($errors->has('account_code') ? ' is-invalid' : ''), 'form' => 'add_account']) !!}
                    <p class="text-danger">{{$errors->first('account_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_name', 'Account Name') !!}
                    {!! Form::text('account_name', '', ['class' => 'form-control'.($errors->has('account_name') ? ' is-invalid' : ''), 'form' => 'add_account']) !!}
                    <p class="text-danger">{{$errors->first('account_name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('short_name', 'Short Name') !!}
                    {!! Form::text('short_name', '', ['class' => 'form-control'.($errors->has('short_name') ? ' is-invalid' : ''), 'form' => 'add_account']) !!}
                    <p class="text-danger">{{$errors->first('short_name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_id', 'Discount') !!}
                    {!! Form::select('discount_id', $discounts, null, ['class' => 'form-control'.($errors->has('discount_id') ? ' is-invalid' : ''), 'form' => 'add_account']) !!}
                    <p class="text-danger">{{$errors->first('discount_id')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Account', ['class' => 'btn btn-primary', 'form' => 'add_account']) !!}
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