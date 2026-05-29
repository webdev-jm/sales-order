@extends('adminlte::page')

@section('title')
    PAF ADD
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>ADD PAF</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('paf.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')
    <livewire:paf.create/>

    <div class="modal fade" id="modal-summary">
        <div class="modal-dialog modal-lg">
            <livewire:paf.detail/>
        </div>
    </div>

    <div class="modal fade" id="modal-pre-plan">
        <div class="modal-dialog modal-xl">
            <livewire:paf.pre-plan />
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