@extends('adminlte::page')

@section('title')
    Departments
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Edit Department</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('department.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['department.update', $department->id], 'id' => 'update_department']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Update Department</h3>
    </div>
    <div class="card-body">

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('department_code', 'Department Code') !!}
                    {!! Form::text('department_code', $department->department_code, ['class' => 'form-control'.($errors->has('department_code') ? ' is-invalid' :''), 'placeholder' => 'Department Code', 'form' => 'update_department']) !!}
                    <p class="text-danger">{{$errors->first('department_code')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('department_name', 'Department Name') !!}
                    {!! Form::text('department_name', $department->department_name, ['class' => 'form-control'.($errors->has('department_name') ? ' is-invalid' :''), 'placeholder' => 'Department Name', 'form' => 'update_department']) !!}
                    <p class="text-danger">{{$errors->first('department_name')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('department_head_id', 'Department Head') !!}
                    {!! Form::select('department_head_id', [], NULL, ['class' => 'form-control select2'.($errors->has('department_head_id') ? ' is-invalid' :''), 'form' => 'update_department']) !!}
                    <p class="text-danger">{{$errors->first('department_head_id')}}</p>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('department_admin_id', 'Department Admin') !!}
                    {!! Form::select('department_admin_id', [], NULL, ['class' => 'form-control select2'.($errors->has('department_admin_id') ? ' is-invalid' :''), 'form' => 'update_department']) !!}
                    <p class="text-danger">{{$errors->first('department_admin_id')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Update Department', ['class' => 'btn btn-primary', 'form' => 'update_department']) !!}
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

        $('#department_head_id').select2({
            ajax: { 
                url: '{{route("user.ajax")}}',
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
        
        var head_select = $('#department_head_id');
        $.ajax({
            type:'GET',
            url: '/user/get-ajax/{{$department->department_head_id}}'
        }).then(function(data) {
            console.log(data);
            var option = new Option(data.firstname+' '+data.lastname, data.id, true, true);
            head_select.append(option).trigger('change');

            head_select.trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        });

        $('#department_admin_id').select2({
            ajax: { 
                url: '{{route("user.ajax")}}',
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

        var admin_select = $('#department_admin_id');
        $.ajax({
            type:'GET',
            url: '/user/get-ajax/{{$department->department_admin_id}}'
        }).then(function(data) {
            console.log(data);
            var option = new Option(data.firstname+' '+data.lastname, data.id, true, true);
            admin_select.append(option).trigger('change');

            admin_select.trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection