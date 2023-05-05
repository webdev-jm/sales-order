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
    <h1>Dashboard</h1>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['dashboard'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="row">
    {{-- REMINDERS --}}
    <div class="col-lg-6">
        <livewire:dashboard.reminder-list />
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
