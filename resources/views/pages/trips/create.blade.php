@extends('adminlte::page')

@section('title')
    Trips
@endsection

@section('css')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/datepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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

<livewire:trip.trip-create/>

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