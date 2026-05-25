@extends('adminlte::page')

@section('title')
Activity Plan - Edit
@endsection

@section('css')
<style>
    .select2.select2-container.select2-container--default {
        width: 100% !important;
    }
    .select2-selection.select2-selection--single {
        border: 0;
    }
    .sticky-top {
        top: 58px;
    }
    .search-branch {
        z-index: 999;
    }
</style>
@endsection

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">
        Activity Plan <small class="text-muted">/ Edit</small>
        <span class="badge badge-{{$status_arr[$activity_plan->status]}} ml-1">{{$activity_plan->status}}</span>
    </h1>
    <a href="{{route('mcp.index')}}" class="btn btn-default btn-sm">
        <i class="fa fa-arrow-left mr-1"></i> Back
    </a>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['mcp.update', $activity_plan->id], 'id' => 'update_mcp']) !!}
{!! Form::close() !!}

<div class="card sticky-top shadow-sm">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-lg-8 col-md-6">
                <div class="text-sm">
                    <span class="text-muted">Name:</span>
                    <strong class="ml-1">{{$activity_plan->user->fullName()}}</strong>
                </div>
                @if(!empty($position))
                <div class="text-sm">
                    <span class="text-muted">Position:</span>
                    <strong class="ml-1">{{implode(', ', $position)}}</strong>
                </div>
                @endif
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="d-flex justify-content-end">
                    {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-sm mr-1 btn-submit', 'form' => 'update_mcp']) !!}
                    {!! Form::submit('Submit for Approval', ['class' => 'btn btn-primary btn-sm btn-submit', 'form' => 'update_mcp']) !!}
                    {!! Form::hidden('status', $activity_plan->status, ['form' => 'update_mcp', 'id' => 'status']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <livewire:activity-plan.header/>
    </div>
    <div class="col-12">
        <livewire:activity-plan.detail/>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        // change status base on button clicked
        $('body').on('click', '.btn-submit', function(e) {
            e.preventDefault();
            var status = $(this).val();
            var status_val = '';
            if(status == 'Submit for Approval') {
                if(confirm('Are you sure to submit this mcp?')) {
                    status_val = 'submitted';
                    $('#status').val(status_val);
                    $('#'+$(this).attr('form')).submit();
                }
            } else {
                status_val = 'draft';
                $('#status').val(status_val);
                $('#'+$(this).attr('form')).submit();
            }
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
