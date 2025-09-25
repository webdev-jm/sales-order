@extends('adminlte::page')

@section('title')
    Credit Memo - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Credit Memo / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('cm.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<livewire:credit-memo.create />

@endsection

@section('js')
<script>
    $(function() {
        $('.select').select2();
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
