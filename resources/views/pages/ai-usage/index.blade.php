@extends('adminlte::page')

@section('title')
    AI Usage
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>AI Usage</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('ai settings manage')
            <a href="{{ route('ai-settings.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-sliders-h mr-1"></i> AI Settings
            </a>
        @endcan
        @can('settings')
            <a href="{{ route('setting.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-cog mr-1"></i> Token limits
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
    <livewire:ai-usage.index />
@endsection
