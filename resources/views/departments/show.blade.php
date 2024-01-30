@extends('adminlte::page')

@section('title')
    Departments
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Department</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('department.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            Back
        </a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">DEPARTMENT DETAILS</h3>
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