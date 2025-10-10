@extends('adminlte::page')

@section('title')
    PPU Forms
@endsection

@section('css')
<script src="{{ asset('js/gsap.min.js') }}"></script>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>PPU FORM MULTIPLE UPLOAD</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('ppu.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<livewire:ppu-form.multiple.upload :logged_account="$logged_account" />

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