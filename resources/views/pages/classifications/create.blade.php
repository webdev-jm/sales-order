@extends('adminlte::page')

@section('title')
    Classifications - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Classifications / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('classification.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Classification</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['classification.store'], 'id' => 'add_classification']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('classification_code', 'Classification Code') !!}
                    {!! Form::text('classification_code', '', ['class' => 'form-control'.($errors->has('classification_code') ? ' is-invalid' : ''), 'form' => 'add_classification']) !!}
                    <p class="text-danger">{{$errors->first('classification_code')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('classification_name', 'Classification Name') !!}
                    {!! Form::text('classification_name', '', ['class' => 'form-control'.($errors->has('classification_name') ? ' is-invalid' : ''), 'form' => 'add_classification']) !!}
                    <p class="text-danger">{{$errors->first('classification_name')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Classification', ['class' => 'btn btn-primary', 'form' => 'add_classification']) !!}
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