@extends('adminlte::page')

@section('title')
    Invoice Terms - Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Invoice Terms / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('invoice-term.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Edit Invoice Term</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['invoice-term.udpate', $invoice_term->id], 'id' => 'update_invoice_term']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('term_code', 'Term Code') !!}
                    {!! Form::text('term_code', $invoice_term->term_code, ['class' => 'form-control'.($errors->has('term_code') ? ' is-invalid' : ''), 'form' => 'update_invoice_term']) !!}
                    <p class="text-danger">{{$errors->first('term_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::text('description', $invoice_term->description, ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'form' => 'update_invoice_term']) !!}
                    <p class="text-danger">{{$errors->first('description')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount', 'Discount') !!}
                    {!! Form::number('discount', $invoice_term->discount, ['class' => 'form-control'.($errors->has('discount') ? ' is-invalid' : ''), 'form' => 'update_invoice_term']) !!}
                    <p class="text-danger">{{$errors->first('discount')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_days', 'Discount Days') !!}
                    {!! Form::number('discount_days', $invoice_term->discount_days, ['class' => 'form-control'.($errors->has('discount_days') ? ' is-invalid' : ''), 'form' => 'update_invoice_term']) !!}
                    <p class="text-danger">{{$errors->first('discount_days')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('due_days', 'Due Days') !!}
                    {!! Form::number('due_days', $invoice_term->due_days, ['class' => 'form-control'.($errors->has('due_days') ? ' is-invalid' : ''), 'form' => 'update_invoice_term']) !!}
                    <p class="text-danger">{{$errors->first('due_days')}}</p>
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Invoice Term', ['class' => 'btn btn-primary', 'form' => 'update_invoice_term']) !!}
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