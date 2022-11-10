@extends('adminlte::page')

@section('title')
    MCP
@endsection

@section('css')
<style>
    .select2.select2-container.select2-container--default {
        width: 100% !important;
    }

    .select2-selection.select2-selection--single {
        border: 0;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>MCP</h1>
    </div>
    <div class="col-lg-6 text-right">
        @can('mcp create')
        <a href="{{route('mcp.create')}}" class="btn btn-primary"><i class="fa fa-plus mr-1"></i>Add MCP</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['mcp.index', $search], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">MCP List</h3>
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
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Year</th>
                    <th>Month</th>
                    <th>Status</th>
                    <th>Created at</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($activity_plans as $activity_plan)
                <tr>
                    <td>{{$activity_plan->user->fullName()}}</td>
                    <td>{{$activity_plan->year}}</td>
                    <td>{{$activity_plan->month}}</td>
                    <td>
                        <span class="badge badge-{{$status_arr[$activity_plan->status]}}">{{$activity_plan->status}}</span>
                    </td>
                    <td>{{$activity_plan->created_at->diffForHumans()}}</td>
                    <td class="text-right">
                        <a href="{{route('mcp.show', $activity_plan->id)}}"  title="view"><i class="fas fa-eye text-primary mx-1"></i></a>
                        @if(auth()->user()->can('mcp edit') && $activity_plan->status == 'draft')
                            <a href="{{route('mcp.edit', $activity_plan->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endif
                        @can('mcp delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$activity_plan->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$activity_plans->links()}}
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
