@extends('adminlte::page')

@section('title')
    Holidays
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Holidays</h1>
    </div>
</div>
@endsection

@section('content')
<livewire:holidays.holiday-index />

<div class="modal fade" id="modal-add">
    <div class="modal-dialog">
        <livewire:holidays.holiday-add />
    </div>
</div>
@endsection

@section('plugins.Fullcalendar', true)

@section('js')
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
