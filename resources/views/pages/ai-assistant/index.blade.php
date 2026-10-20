@extends('adminlte::page')

@section('title')
    AI Assistant
@endsection

@section('css')
<style>
    .ai-chat-messages { height: calc(100vh - 380px); min-height: 320px; }
    .direct-chat-msg .direct-chat-text { margin-left: 0; margin-right: 15%; }
    .direct-chat-msg.right .direct-chat-text { margin-left: 15%; margin-right: 0; }
    .direct-chat-text::after, .direct-chat-text::before { display: none; }
    .ai-chat-answer p:last-child, .ai-chat-answer ul:last-child, .ai-chat-answer ol:last-child { margin-bottom: 0; }
    .ai-chat-answer table { width: 100%; margin: .5rem 0; font-size: .9rem; }
    .ai-chat-answer th, .ai-chat-answer td { border-bottom: 1px solid rgba(0, 0, 0, .1); padding: .2rem .4rem; }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>AI Assistant</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('ai usage access')
            <a href="{{ route('ai-usage.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-robot mr-1"></i> AI Usage
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
    <livewire:ai-assistant.chat />
@endsection
