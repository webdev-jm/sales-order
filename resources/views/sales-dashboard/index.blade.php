@extends('adminlte::page')

@section('title')
    Sales Dashboard
@endsection

@section('css')
<style>
</style>
<script src="https://cdn.tailwindcss.com"></script>
@endsection

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>Sales Dashboard</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{route('dashboard')}}" class="btn btn-primary">DASHBOARD</a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        
    </div>
</div>

<div class="row">
    <div class="col-lg-3">
        <livewire:sales-dashboard.stats/>
    </div>

    {{-- CHARTS --}}
    <div class="col-lg-9">
        <div class="row">
            <div class="col-lg-4">
                <livewire:sales-dashboard.chart-company/>
            </div>
            <div class="col-lg-4">
                <livewire:sales-dashboard.chart-business-unit/>
            </div>
            <div class="col-lg-4">
                <livewire:sales-dashboard.chart-core/>
            </div>
        </div>

        {{-- SALES VS TARAGET --}}
        <div class="row">
            <div class="col-12">
                <livewire:sales-dashboard.chart-sales-vs-target/>
            </div>
        </div>

        {{-- PO VS INVOICED VS UNSERVED --}}
        <div class="row">
            <div class="col-12">
                <livewire:sales-dashboard.chart-po-invoiced/>
            </div>
        </div>
    </div>
</div>

{{-- TABLES --}}
<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-none bg-transparent">
            <div class="card-header border-0 pb-0">
                <span class="card-title bg-gradient-to-t from-gray-900 via-gray-600 to-gray-900 text-white px-7 py-1 border-2 border-gray-700 border-solid text-3xl shadow-xl">BEVI</span>
            </div>
            <div class="card-body p-0">
                <livewire:sales-dashboard.table-fast-moving/>

                <livewire:sales-dashboard.table-sales-volume/>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-none bg-transparent">
            <div class="card-header border-0 pb-0">
                <span class="card-title bg-gradient-to-t from-gray-900 via-gray-600 to-gray-900 text-white px-7 py-1 border-2 border-gray-700 border-solid text-3xl shadow-xl">BEVA</span>
            </div>
            <div class="card-body p-0">
                <livewire:sales-dashboard.table-fast-moving-beva/>

                <livewire:sales-dashboard.table-sales-volume-beva/>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS --}}
<div class="row">
    <div class="col-lg-6">
        <livewire:sales-dashboard.chart-nka/>
    </div>
    <div class="col-lg-6">
        <livewire:sales-dashboard.chart-rdg/>
    </div>
</div>
@endsection

@section('js')

@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
