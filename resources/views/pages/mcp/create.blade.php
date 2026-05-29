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
<div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Activity Plan <small class="text-muted">/ Create</small></h1>
    <a href="{{route('mcp.index')}}" class="btn btn-default btn-sm">
        <i class="fa fa-arrow-left mr-1"></i> Back
    </a>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['mcp.store'], 'id' => 'add_mcp']) !!}
{!! Form::close() !!}

<div class="card sticky-top shadow-sm">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-4">
                <div class="text-sm">
                    <span class="text-muted">Name:</span>
                    <strong class="ml-1">{{auth()->user()->fullName()}}</strong>
                </div>
                @if(!empty($position))
                <div class="text-sm">
                    <span class="text-muted">Position:</span>
                    <strong class="ml-1">{{implode(', ', $position)}}</strong>
                </div>
                @endif
            </div>
            <div class="col-lg-6 col-md-8">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-outline-secondary btn-sm mr-1" id="btn-upload" type="button">
                        <i class="fa fa-upload mr-1"></i> Upload
                    </button>
                    {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-sm mr-1 btn-submit', 'form' => 'add_mcp']) !!}
                    {!! Form::submit('Submit for Approval', ['class' => 'btn btn-primary btn-sm btn-submit', 'form' => 'add_mcp']) !!}
                    {!! Form::hidden('status', 'draft', ['form' => 'add_mcp', 'id' => 'status']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

@error('status')
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-ban"></i> <strong>Error!</strong> {{$message}}
</div>
@enderror

@error('line_errors')
    <div class="alert alert-danger">{{$message}}</div>
@enderror

<div class="row">
    <div class="col-12">
        <livewire:activity-plan.header/>
    </div>
    <div class="col-12">
        <livewire:activity-plan.detail/>
    </div>
</div>

{{-- Activities Modal --}}
<div class="modal fade" id="modal-activities">
    <div class="modal-dialog modal-lg">
        <livewire:activity-plan.activities />
    </div>
</div>

{{-- Upload Modal --}}
<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h4 class="modal-title"><i class="fa fa-upload mr-2"></i>Upload Activity Plan Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['mcp.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
                {!! Form::close() !!}

                <div class="form-group">
                    <label>Select File</label>
                    <div class="custom-file">
                        {!! Form::file('upload_file', ['class' => 'custom-file-input'.($errors->has('upload_file') ? ' is-invalid' : ''), 'form' => 'upload_form', 'accept' => '.xlsx, .xls, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']) !!}
                        {!! Form::label('upload_file', 'Choose file...', ['class' => 'custom-file-label']) !!}
                    </div>
                    @if($errors->has('upload_file'))
                        <p class="text-danger mt-1">{{$errors->first('upload_file')}}</p>
                    @endif
                </div>

                <a href="{{asset('/assets/SMS Activity Plan Upload Format.xlsx')}}" class="btn btn-outline-success btn-sm">
                    <i class="fa fa-file-excel mr-1"></i> Download Format
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Upload', ['class' => 'btn btn-primary', 'form' => 'upload_form']) !!}
            </div>
        </div>
    </div>
</div>

{{-- Loading Modal --}}
<div class="modal fade" id="loadingModal" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="loadingModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fa fa-spinner fa-spin fa-2x text-primary mb-3 d-block"></i>
                <strong>Uploading, please wait...</strong>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        // activities
        $('body').on('click', '.btn-activities', function(e) {
            e.preventDefault();
            var year = $(this).data('year');
            var month = $(this).data('month');
            var date = $(this).data('date');
            var key = $(this).data('key');
            Livewire.emit('setActivities', year, month, date, key);
            $('#modal-activities').modal('show');
        });

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

        // upload details
        $('#btn-upload').on('click', function(e) {
            e.preventDefault();
            $('#modal-upload').modal('show');
        });
    })
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
