@extends('adminlte::page')

@section('title')
    Territories - Add
@endsection

@section('css')
<style>
    .hover-select {
        cursor: pointer;
    }

    .hover-select:hover {
        background: #e3fdff;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Territories / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('territory.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Territory</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['territory.store'], 'id' => 'save_territory']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('territory_code', 'Territory Code') !!}
                    {!! Form::text('territory_code', '', ['class' => 'form-control'.($errors->has('territory_code') ? ' is-invalid' : ''), 'form' => 'save_territory']) !!}
                    <p class="text-danger">{{$errors->first('territory_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('territory_name', 'Territory Name') !!}
                    {!! Form::text('territory_name', '', ['class' => 'form-control'.($errors->has('territory_name') ? ' is-invalid' : ''), 'form' => 'save_territory']) !!}
                    <p class="text-danger">{{$errors->first('territory_name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <livewire:territories.district-assign />
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_id', 'User') !!}
                    {!! Form::select('user_id', [], null, ['class' => 'form-control'.($errors->has('user_id') ? ' is-invalid' : ''), 'form' => 'add_sales_person']) !!}
                    <p class="text-danger">{{$errors->first('user_id')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Territory', ['class' => 'btn btn-primary', 'form' => 'add_territory']) !!}
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