@extends('adminlte::page')

@section('title')
    Upload Template - Create
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Upload Template - Create</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('upload-template.index')}}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
    </div>
</div>
@endsection

@section('content')
    <livewire:upload-templates.form/>
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