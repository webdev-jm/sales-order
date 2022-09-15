@extends('adminlte::page')

@section('title')
    Companies - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Companies / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('company.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Company</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['company.store'], 'id' => 'add_company']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('name', 'Name') !!}
                    {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_company']) !!}
                    <p class="text-danger">{{$errors->first('name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('order_limit', 'Order Limit') !!}
                    {!! Form::number('order_limit', '', ['class' => 'form-control'.($errors->has('order_limit') ? ' is-invalid' : ''), 'form' => 'add_company']) !!}
                    <p class="text-danger">{{$errors->first('order_limit')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Company', ['class' => 'btn btn-primary', 'form' => 'add_company']) !!}
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