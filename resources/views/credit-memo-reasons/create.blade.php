@extends('adminlte::page')
@php
    use Collective\Html\FormFacade as Form;
@endphp

@section('title')
    CM Reasons - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>CM Reasons / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('cm-reason.index')}}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i>
            {{__('Back')}}
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Add CM Reason</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'post', 'route' => ['cm-reason.store'], 'id' => 'add_cm_reason']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('reason_code', 'Reason Code') !!}
                    {!! Form::text('reason_code', null, ['class' => 'form-control', 'form' => 'add_cm_reason', 'required']) !!}
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('reason_description', 'Description') !!}
                    {!! Form::text('reason_description', null, ['class' => 'form-control', 'form' => 'add_cm_reason', 'required']) !!}
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add CM Reason', ['class' => 'btn btn-primary', 'form' => 'add_cm_reason']) !!}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('.select').select2();
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
