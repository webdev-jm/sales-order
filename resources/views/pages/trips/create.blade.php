@extends('adminlte::page')

@section('title')
    Trips
@endsection

@section('css')
<script src="{{ asset('vendor/tailwindcss/tailwindcss.js') }}"></script>
<script src="{{ asset('vendor/flowbite/datepicker.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('vendor/animate-css/animate.min.css') }}" />
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