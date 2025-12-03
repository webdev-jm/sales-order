@extends('adminlte::page')

@section('title')
    Dashboard
@endsection

@section('css')
<style>
    .small-box .inner {
        border: solid 3px rgb(98, 98, 98);
    }
    .small-box:hover .inner {
        border: solid 3px rgb(76, 145, 255);
        cursor: pointer;
    }
    .h-90 {
        height: 90% !important;
    }
</style>
@endsection

@section('content_header')

    <div class="row">
        <div class="col-lg-6">
            <h1>Dashboard</h1>
        </div>
        @can('sales dashboard')
        {{-- <div class="col-lg-6 text-right">
            <a href="{{route('sales-dashboard.index')}}" class="btn btn-primary">SALES DASHBOARD</a>
        </div> --}}
        @endcan
    </div>
@endsection

@section('content')

<div class="row">
    {{-- REMINDERS --}}
    <div class="col-lg-6">
        <livewire:dashboard.reminder-list />
    </div>

</div>


@endsection

@section('js')

@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
