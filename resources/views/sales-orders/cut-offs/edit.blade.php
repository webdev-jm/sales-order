@extends('adminlte::page')

@section('title')
    Sales Order Cut-offs
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Edit Cut-off</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('cut-off.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'post', 'route' => ['cut-off.update', $cut_off->id], 'id' => 'update_cut_off']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3>Add Cut-off</h3>
    </div>
    <div class="card-body">

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('date', 'Date') !!}
                    {!! Form::date('date', $cut_off->date, ['class' => 'form-control'.($errors->has('date') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                    <p class="text-danger">{{$errors->first('date')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('time', 'Time') !!}
                    {!! Form::time('time', $cut_off->time, ['class' => 'form-control'.($errors->has('time') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                    <p class="text-danger">{{$errors->first('time')}}</p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="form-group">
                    {!! Form::label('message', 'Message') !!}
                    {!! Form::textarea('message', $cut_off->message, ['class' => 'form-control'.($errors->has('message') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                    <p class="text-danger">{{$errors->first('message')}}</p>
                </div>
            </div>

        </div>
        
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Cut-off', ['class' => 'btn btn-primary', 'form' => 'update_cut_off']) !!}
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
