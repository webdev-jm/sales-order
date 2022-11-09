@extends('adminlte::page')

@section('title')
    MCP
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>MCP</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('mcp.index')}}" class="btn btn-primary"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Activity Plan for the Month of: <span class="font-weight-bold text-uppercase">{{date('F', strtotime($activity_plan->year.'-'.$activity_plan->month.'-01'))}} {{$activity_plan->year}}</span></h3>
    </div>
    <div class="card-body">
        <p>
            <b>NAME:</b> {{$activity_plan->user->fullName()}}<br>
            @if(!empty($position))
            <b>POSITION:</b> {{implode(', ', $position)}}
            @endif
        </p>
        
        <div class="row">
            <div class="col-lg-8">
                <label>Objectives</label>
                <p>
                    {{$activity_plan->objectives}}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- calendar --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"></h3>
    </div>
</div>
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
