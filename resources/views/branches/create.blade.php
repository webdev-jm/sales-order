@extends('adminlte::page')

@section('title')
    Branches - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Branches / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('branch.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Branch</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['branch.store'], 'id' => 'add_branch']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_id', 'Account') !!}
                    {!! Form::select('account_id', [], null, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                    <p class="text-danger">{{$errors->first('account_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('branch_code', 'Branch Code') !!}
                    {!! Form::text('branch_code', '', ['class' => 'form-control'.($errors->has('branch_code') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                    <p class="text-danger">{{$errors->first('branch_code')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('branch_name', 'Branch Name') !!}
                    {!! Form::text('branch_name', '', ['class' => 'form-control'.($errors->has('branch_name') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                    <p class="text-danger">{{$errors->first('branch_name')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('region_id', 'Region') !!}
                    {!! Form::select('region_id', $regions, null, ['class' => 'form-control'.($errors->has('region_id') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                    <p class="text-danger">{{$errors->first('region_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('classification_id', 'Classification') !!}
                    {!! Form::select('classification_id', $classifications, null, ['class' => 'form-control'.($errors->has('classification_id') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                    <p class="text-danger">{{$errors->first('classification_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('area_id', 'Area') !!}
                    {!! Form::select('area_id', $areas, null, ['class' => 'form-control'.($errors->has('area_id') ? ' is-invalid' : ''), 'form' => 'add_branch']) !!}
                    <p class="text-danger">{{$errors->first('area_id')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Branch', ['class' => 'btn btn-primary', 'form' => 'add_branch']) !!}
    </div>
</div>

@endsection

@section('plugins.Select2', true)

@section('js')
<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#account_id').select2({
            ajax: { 
                url: '{{route("account.ajax")}}',
                type: "POST",
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
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