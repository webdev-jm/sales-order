@extends('adminlte::page')

@section('title')
    Ship Address Mapping
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Ship Address Mapping</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('ship-address-mapping.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')
<livewire:ship-address-mapping.edit :ship_address_mapping="$ship_address_mapping"/>
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