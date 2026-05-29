@extends('adminlte::page')

@section('title')
    Salesmen - Create
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Salesmen - Create</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('salesman.index')}}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'post', 'route' => ['salesman.store'], 'id' => 'add_salesman']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Saleman</h3>
    </div>
    <div class="card-body">

        <div class="row">
            {{-- code --}}
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('code', 'Code') !!}
                    {!! Form::text('code', '', ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'add_salesman']) !!}
                    @if($errors->has('code'))
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    @endif
                </div>
            </div>

            {{-- name --}}
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('name', 'Name') !!}
                    {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_salesman']) !!}
                    @if($errors->has('name'))
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Salesman', ['class' => 'btn btn-primary', 'form' => 'add_salesman']) !!}
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