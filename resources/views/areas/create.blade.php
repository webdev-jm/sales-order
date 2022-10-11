@extends('adminlte::page')

@section('title')
    Area - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Area / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('area.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Area</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['area.store'], 'id' => 'add_area']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('area_code', 'Area Code') !!}
                    {!! Form::text('area_code', '', ['class' => 'form-control'.($errors->has('area_code') ? ' is-invalid' : ''), 'form' => 'add_area']) !!}
                    <p class="text-danger">{{$errors->first('area_code')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('area_name', 'Area Name') !!}
                    {!! Form::text('area_name', '', ['class' => 'form-control'.($errors->has('area_name') ? ' is-invalid' : ''), 'form' => 'add_area']) !!}
                    <p class="text-danger">{{$errors->first('area_name')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Area', ['class' => 'btn btn-primary', 'form' => 'add_area']) !!}
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