@extends('adminlte::page')

@section('title')
    Accounts - Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Accounts / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('account.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Edit Account</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'post', 'route' => ['account.update', encrypt($account->id)], 'id' => 'update_account']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('company_id', 'Company') !!}
                    {!! Form::select('company_id', $companies, $account->company_id, ['class' => 'form-control'.($errors->has('company_id') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('company_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_code', 'Account Code') !!}
                    {!! Form::text('account_code', $account->account_code, ['class' => 'form-control'.($errors->has('account_code') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('account_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_name', 'Account Name') !!}
                    {!! Form::text('account_name', $account->account_name, ['class' => 'form-control'.($errors->has('account_name') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('account_name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('short_name', 'Short Name') !!}
                    {!! Form::text('short_name', $account->short_name, ['class' => 'form-control'.($errors->has('short_name') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('short_name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_id', 'Discount') !!}
                    {!! Form::select('discount_id', $discounts, $account->discount_id, ['class' => 'form-control select'.($errors->has('discount_id') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('discount_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('price_code', 'Price Code') !!}
                    {!! Form::select('price_code', $price_codes, $account->price_code, ['class' => 'form-control select'.($errors->has('price_code') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('price_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('invoice_term_id', 'Invoice Term') !!}
                    {!! Form::select('invoice_term_id', $invoice_terms, $account->invoice_term_id, ['class' => 'form-control select'.($errors->has('invoice_term_id') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('invoice_term_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ship_to_address1', 'Ship To Address 1') !!}
                    {!! Form::text('ship_to_address1', $account->ship_to_address1, ['class' => 'form-control'.($errors->has('ship_to_address1') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('ship_to_address1')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ship_to_address2', 'Ship To Address 2') !!}
                    {!! Form::text('ship_to_address2', $account->ship_to_address2, ['class' => 'form-control'.($errors->has('ship_to_address2') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('ship_to_address2')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ship_to_address3', 'Ship To Address 3') !!}
                    {!! Form::text('ship_to_address3', $account->ship_to_address3, ['class' => 'form-control'.($errors->has('ship_to_address3') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('ship_to_address3')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('postal_code', 'Postal Code') !!}
                    {!! Form::text('postal_code', $account->postal_code, ['class' => 'form-control'.($errors->has('postal_code') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('postal_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('tax_number', 'Tax Number') !!}
                    {!! Form::text('tax_number', $account->tax_number, ['class' => 'form-control'.($errors->has('tax_number') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('tax_number')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('po_process_date', 'PO Processing Date') !!}
                    {!! Form::number('po_process_date', $account->po_process_date, ['class' => 'form-control'.($errors->has('po_process_date') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('po_process_date')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('po_prefix', 'PO Prefix') !!}
                    {!! Form::text('po_prefix', $account->po_prefix, ['class' => 'form-control'.($errors->has('po_prefix') ? ' is-invalid' : ''), 'form' => 'update_account']) !!}
                    <p class="text-danger">{{$errors->first('po_prefix')}}</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    <label>On Hold</label>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('on_hold', 0, $account->on_hold == 0, ['class' => 'custom-control-input', 'id' => 'no_radio', 'form' => 'update_account']) !!}
                        {!! Form::label('no_radio', 'NO', ['class' => 'custom-control-label']) !!}
                    </div>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('on_hold', 1, $account->on_hold == 1, ['class' => 'custom-control-input', 'id' => 'yes_radion', 'form' => 'update_account']) !!}
                        {!! Form::label('yes_radion', 'YES', ['class' => 'custom-control-label']) !!}
                    </div>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Account', ['class' => 'btn btn-primary', 'form' => 'update_account']) !!}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('.select').select2();
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection