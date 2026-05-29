@extends('adminlte::page')

@section('title')
    Districts - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Districts / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('district.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add District</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['district.store'], 'id' => 'add_district']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('district_code', 'District Code') !!}
                    {!! Form::text('district_code', '', ['class' => 'form-control'.($errors->has('district_code') ? ' is-invalid' : ''), 'form' => 'add_district']) !!}
                    <p class="text-danger">{{$errors->first('district_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('district_name', 'District Name') !!}
                    {!! Form::text('district_name', '', ['class' => 'form-control'.($errors->has('district_name') ? ' is-invalid' : ''), 'form' => 'add_district']) !!}
                    <p class="text-danger">{{$errors->first('district_name')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add District', ['class' => 'btn btn-primary', 'form' => 'add_district']) !!}
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