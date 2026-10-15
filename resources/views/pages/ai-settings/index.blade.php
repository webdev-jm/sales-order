@extends('adminlte::page')

@section('title')
    AI Settings
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>AI Settings</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('ai usage access')
            <a href="{{ route('ai-usage.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-chart-bar mr-1"></i> AI Usage
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
    <livewire:ai-settings.index />
@endsection
