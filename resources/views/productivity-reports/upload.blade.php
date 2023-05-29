@extends('adminlte::page')

@section('title')
    Productivity Reports / Upload
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Productivity Reports / Upload</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('productivity report upload')
            <a href="{{route('productivity-report.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Back</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['productivity-report.store'], 'id' => 'save_upload']) !!}
{!! Form::close() !!}

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Productivity Report Upload</h3>
    </div>
    <div class="card-body">

        <div class="row">

            <div class="col-lg-2">
                <div class="form-group">
                    {!! Form::label('year', 'Year') !!}
                    {!! Form::number('year', date('Y'), ['class' => 'form-control', 'form' => 'save_upload']) !!}
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group">
                    {!! Form::label('month', 'Month') !!}
                    {!! Form::number('month', date('n'), ['class' => 'form-control', 'form' => 'save_upload']) !!}
                </div>
            </div>

            <div class="col-lg-2">
                <div class="form-group">
                    {!! Form::label('week', 'Week') !!}
                    {!! Form::number('week', 1, ['class' => 'form-control', 'form' => 'save_upload']) !!}
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Save Productivity Report', ['class' => 'btn btn-primary', 'form' => 'save_upload']) !!}
    </div>
</div>

<livewire:productivity-report.details />
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