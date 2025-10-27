@extends('adminlte::page')

@section('title')
    RUD - Edit
@endsection

@section('css')
@endsection

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>
                RUD / Edit
                <span type="button" class="badge badge-{{ $status_arr[$credit_memo->status] }}">
                    {{ strtoupper($credit_memo->status) }}
                </span>
            </h1>

        </div>
        <div class="col-md-6 text-right">
            <a href="{{route('cm.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
        </div>
    </div>
@endsection

@section('content')
    <livewire:credit-memo.edit :credit_memo="$credit_memo"/>
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
