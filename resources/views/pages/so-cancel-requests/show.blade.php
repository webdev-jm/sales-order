@extends('adminlte::page')

@section('title')
    SO Cancel Request - {{ $cancelRequest->request_number }}
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>SO Cancel Requests / {{ $cancelRequest->request_number }}</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{ route('so-cancel-request.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{ __('Back') }}</a>
    </div>
</div>
@endsection

@section('content')
    <livewire:sales-order-cancel-request.show :cancel-request-id="$cancelRequest->id" />
@endsection
