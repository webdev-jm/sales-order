@extends('adminlte::page')

@section('title')
    RUD - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>RUD / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('cm.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<livewire:credit-memo.create />

<div class="modal fade" id="rud-summary-modal">
    <div class="modal-dialog modal-xl">
        <livewire:credit-memo.summary/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {

        $('body').on('click', '#btn-summary-modal', function() {
            $('#rud-summary-modal').modal('show');
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
