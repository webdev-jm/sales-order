@extends('adminlte::page')

@section('title')
    Activity Plan - Create
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
<div class="row">
    <div class="col-lg-6">
        <h1>Activity Plan / Create</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('mcp.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['mcp.store'], 'id' => 'add_mcp']) !!}
{!! Form::close() !!}

<div class="card sticky-top">
    <div class="card-header">
        
        <div class="row">
            <div class="col-lg-8 col-md-6">
                <b>NAME:</b> {{auth()->user()->fullName()}}<br>
                @if(!empty($position))
                <b>POSITION:</b> {{implode(', ', $position)}}
                @endif
            </div>
            <div class="col-lg-4 col-md-6 text-right">
                <div class="row">
                    <div class="col-lg-6">
                        {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-block btn-submit mb-1', 'form' => 'add_mcp']) !!}
                    </div>
                    <div class="col-lg-6">
                        {!! Form::submit('Submit for Approval', ['class' => 'btn btn-primary btn-block btn-submit', 'form' => 'add_mcp']) !!}
                    </div>
                    {!! Form::hidden('status', 'draft', ['form' => 'add_mcp', 'id' => 'status']) !!}
                </div>
            </div>
        </div>
        
    </div>
</div>

@error('status')
<div class="alert alert-danger">
    <h5>
        <i class="icon fas fa-ban"></i>
        Error
    </h5>
    {{$message}}
</div>
@enderror

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
