@extends('adminlte::page')

@section('title')
    Remittances
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Remittances</h1>
    </div>
    <div class="col-md-6 text-right">
    </div>
</div>
@endsection

@section('content')
    <livewire:remittances.upload/>
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