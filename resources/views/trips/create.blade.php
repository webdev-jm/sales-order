@extends('adminlte::page')

@section('title')
    Trips
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>ADD TRIP</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('trip.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['trip.index'], 'id' => 'search_form']) !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">CREATE TRIP</h3>
        <div class="card-tools">
            
        </div>
    </div>
    <div class="card-body">

        

    </div>
    <div class="card-footer">
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