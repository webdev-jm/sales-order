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
            <div class="col-lg-6 col-md-4">
                <b>NAME:</b> {{auth()->user()->fullName()}}<br>
                @if(!empty($position))
                <b>POSITION:</b> {{implode(', ', $position)}}
                @endif
            </div>
            <div class="col-lg-6 col-md-8 text-right">
                <div class="row">
                    <div class="col-lg-4">
                        <button class="btn btn-success btn-block" id="btn-upload"><i class="fa fa-upload mr-1"></i> Upload</button>
                    </div>
                    <div class="col-lg-4">
                        {!! Form::submit('Save as Draft', ['class' => 'btn btn-secondary btn-block btn-submit mb-1', 'form' => 'add_mcp']) !!}
                    </div>
                    <div class="col-lg-4">
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

@error('line_errors')
    <p>{{$message}}</p>
@enderror

<div class="row">
    <div class="col-12">
        <livewire:activity-plan.header/>
    </div>
    
    <div class="col-12">
        <livewire:activity-plan.detail/>
    </div>
</div>

<div class="modal fade" id="modal-activities">
    <div class="modal-dialog modal-lg">
        <livewire:activity-plan.activities />
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Activity Plan Details</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['mcp.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
                {!! Form::close() !!}

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="custom-file">
                                {!! Form::file('upload_file', ['class' => 'custom-file-input'.($errors->has('upload_file') ? ' is-invalid' : ''), 'form' => 'upload_form']) !!}
                                {!! Form::label('upload_file', 'Upload File', ['class' => 'custom-file-label']) !!}
                            </div>
                            <p class="text-danger">{{$errors->first('upload_file')}}</p>
                        </div>
                    </div>

                    <div class="col-12">
                        <a href="{{asset('/assets/SMS Activity Plan Upload Format.xlsx')}}" class="">Download format <i class="fa fa-file-excel text-success ml-1"></i></a>
                    </div>
                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Upload', ['class' => 'btn btn-primary', 'form' => 'upload_form']) !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loadingModal" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="loadingModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="exampleModalLongTitle">UPLOADING......</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <i class="fa fa-spinner fa-spin fa-xl"></i>
                    </div>
                </div>
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
