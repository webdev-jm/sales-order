@extends('adminlte::page')

@section('title')
    Activity Plans
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
<div class="d-flex justify-content-between align-items-end">
    <h1 class="mb-0">Activity Plans</h1>
    @can('mcp create')
    <a href="{{route('mcp.create')}}" class="btn btn-primary btn-sm">
        <i class="fa fa-plus mr-1"></i> Add Activity Plan
    </a>
    @endcan
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['mcp.index', $search], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<livewire:activity-plan.submit-report/>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Activity Plan List</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 200px;">
                {!! Form::text('search', $search, ['class' => 'form-control', 'placeholder' => 'Search...', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary" form="search_form">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th class="pl-3">User</th>
                    <th class="text-center">Year</th>
                    <th class="text-center">Month</th>
                    <th class="text-center">Status</th>
                    <th>Created At</th>
                    <th class="text-center" style="width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activity_plans as $activity_plan)
                <tr>
                    <td class="align-middle pl-3 font-weight-bold">{{$activity_plan->user->fullName()}}</td>
                    <td class="align-middle text-center">{{$activity_plan->year}}</td>
                    <td class="align-middle text-center">{{$activity_plan->month}}</td>
                    <td class="align-middle text-center">
                        <span class="badge badge-pill badge-{{$status_arr[$activity_plan->status]}} px-3">
                            {{$activity_plan->status}}
                        </span>
                    </td>
                    <td class="align-middle text-muted small">{{$activity_plan->created_at}}</td>
                    <td class="align-middle text-center">
                        @if(auth()->user()->can('mcp edit') && in_array($activity_plan->status, ['draft', 'returned', 'rejected']))
                            <a href="{{route('mcp.edit', $activity_plan->id)}}" title="Edit" class="btn btn-xs btn-outline-success">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                        <a href="{{route('mcp.show', $activity_plan->id)}}" title="View" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('mcp delete')
                            <a href="#" title="Delete" class="btn btn-xs btn-outline-danger btn-delete" data-id="{{$activity_plan->id}}">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No activity plans found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{$activity_plans->links()}}
    </div>
</div>

<div class="modal fade" id="modal-delete">
    <div class="modal-dialog">
        <livewire:confirm-delete/>
    </div>
</div>
@endsection

@section('js')
<script>
   $(function() {
        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeleteModel', 'ActivityPlan', id);
            $('#modal-delete').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
