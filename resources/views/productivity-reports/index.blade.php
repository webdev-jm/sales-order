@extends('adminlte::page')

@section('title')
    Productivity Reports
@endsection

@section('css')
<style>
    .table-sm td {
        padding: 1px !important;
        vertical-align: middle !important;
    }
    .table-sm th {
        padding: 3px !important;
        vertical-align: middle !important;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Productivity Reports</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('productivity report upload')
            <a href="{{route('productivity-report.upload')}}" class="btn btn-primary"><i class="fas fa-download mr-1"></i>Upload</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<livewire:productivity-report.report />
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