@extends('adminlte::page')

@section('title')
    MCP
@endsection

@section('css')
<style>
    .select2.select2-container.select2-container--default {
        width: 100% !important;
    }

    .select2-selection.select2-selection--single {
        border: 0;
    }
</style>
@endsection

@section('content_header')
    <h1>MCP</h1>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <livewire:activity-plan.header/>
    </div>
    
    <div class="col-12">
        <livewire:activity-plan.detail/>
    </div>
</div>


@endsection

@section('js')
<script>
   $(function() {
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
