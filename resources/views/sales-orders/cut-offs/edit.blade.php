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

            <div class="col-lg-6">

                <div class="row">
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('start_date', 'Start Date') !!}
                            {!! Form::date('start_date', date('Y-m-d', $cut_off->start_date), ['class' => 'form-control'.($errors->has('start_date') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                            <p class="text-danger">{{$errors->first('start_date')}}</p>
                        </div>
                    </div>
        
                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('start_time', 'Time') !!}
                            {!! Form::time('start_time', date('H:i:s', $cut_off->start_date), ['class' => 'form-control'.($errors->has('start_time') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                            <p class="text-danger">{{$errors->first('start_time')}}</p>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('end_date', 'End Date') !!}
                            {!! Form::date('end_date', date('Y-m-d', $cut_off->end_date), ['class' => 'form-control'.($errors->has('end_date') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                            <p class="text-danger">{{$errors->first('end_date')}}</p>
                        </div>
                    </div>
        
                    <div class="col-lg-6">
                        <div class="form-group">
                            {!! Form::label('end_time', 'Time') !!}
                            {!! Form::time('end_time', date('H:i:s', $cut_off->end_date), ['class' => 'form-control'.($errors->has('end_time') ? ' is-invalid' : ''), 'form' => 'update_cut_off']) !!}
                            <p class="text-danger">{{$errors->first('end_time')}}</p>
                        </div>
                    </div>

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
