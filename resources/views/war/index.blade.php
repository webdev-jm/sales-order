@extends('adminlte::page')

@section('title')
    Weekly Activity Reports
@endsection

@section('css')
<style>
    
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Activity Reports</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('war create')
        <a href="{{route('war.create')}}" class="btn btn-primary"><i class="fa fa-plus mr-1"></i>Add Weekly Activity Report</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['war.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Activity Reports</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Area</th>
                    <th>Date Submitted</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Status</th>
                    <th>Created at</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($weekly_activity_reports as $weekly_activity_report)
                <tr>
                    <td>{{$weekly_activity_report->user->fullName()}}</td>
                    <td>{{'['.$weekly_activity_report->area->area_code.'] '.$weekly_activity_report->area->area_name}}</td>
                    <td>{{$weekly_activity_report->date_submitted}}</td>
                    <td>{{$weekly_activity_report->date_from}}</td>
                    <td>{{$weekly_activity_report->date_to}}</td>
                    <td>
                        <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span>
                    </td>
                    <td>{{$weekly_activity_report->created_at->diffForHumans()}}</td>
                    <td class="text-right">
                        @if(auth()->user()->can('war edit') && ($weekly_activity_report->status == 'draft' || $weekly_activity_report->status == 'rejected'))
                            <a href="{{route('war.edit', $weekly_activity_report->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endif
                        <a href="{{route('war.show', $weekly_activity_report->id)}}" title="view details"><i class="fa fa-eye text-primary"></i></a>
                        @can('war delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$weekly_activity_report->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$weekly_activity_reports->links()}}
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
