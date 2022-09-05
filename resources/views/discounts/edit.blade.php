@extends('adminlte::page')

@section('title')
    Discounts - Edit

@section('title')
    Discounts - Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Discounts / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('discount.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Discount</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['discount.update', $discount->id], 'id' => 'update_discount']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_code', 'Discount Code') !!}
                    {!! Form::text('discount_code', $discount->discount_code, ['class' => 'form-control'.($errors->has('discount_code') ? ' is-invalid' : ''), 'form' => 'update_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::text('description', $discount->description, ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'form' => 'update_discount']) !!}
                    <p class="text-danger">{{$errors->first('description')}}</p>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_1', 'Discount Percentage 1') !!}
                    {!! Form::number('discount_1', $discount->discount_1, ['class' => 'form-control'.($errors->has('discount_1') ? ' is-invalid' : ''), 'form' => 'update_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_1')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_2', 'Discount Percentage 2') !!}
                    {!! Form::number('discount_2', $discount->discount_2, ['class' => 'form-control'.($errors->has('discount_2') ? ' is-invalid' : ''), 'form' => 'update_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_2')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_3', 'Discount Percentage 3') !!}
                    {!! Form::number('discount_3', $discount->discount_3, ['class' => 'form-control'.($errors->has('discount_3') ? ' is-invalid' : ''), 'form' => 'update_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_3')}}</p>
                </div>
            </div>

        </div>
        
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Discount', ['class' => 'btn btn-primary', 'form' => 'update_discount']) !!}
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
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Discounts / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('discount.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Discount</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['discount.store'], 'id' => 'add_discount']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_code', 'Discount Code') !!}
                    {!! Form::text('discount_code', '', ['class' => 'form-control'.($errors->has('discount_code') ? ' is-invalid' : ''), 'form' => 'add_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::text('description', '', ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'form' => 'add_discount']) !!}
                    <p class="text-danger">{{$errors->first('description')}}</p>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_1', 'Discount Percentage 1') !!}
                    {!! Form::number('discount_1', '', ['class' => 'form-control'.($errors->has('discount_1') ? ' is-invalid' : ''), 'form' => 'add_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_1')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_2', 'Discount Percentage 2') !!}
                    {!! Form::number('discount_2', '', ['class' => 'form-control'.($errors->has('discount_2') ? ' is-invalid' : ''), 'form' => 'add_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_2')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('discount_3', 'Discount Percentage 3') !!}
                    {!! Form::number('discount_3', '', ['class' => 'form-control'.($errors->has('discount_3') ? ' is-invalid' : ''), 'form' => 'add_discount']) !!}
                    <p class="text-danger">{{$errors->first('discount_3')}}</p>
                </div>
            </div>

        </div>
        
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Discount', ['class' => 'btn btn-primary', 'form' => 'add_discount']) !!}
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