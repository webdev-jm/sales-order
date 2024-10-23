@extends('adminlte::page')

@section('title')
    Users - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Users / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('user access')
        <a href="{{route('user.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add User</h3>
    </div>
    <div class="card-body">

        {!! Form::open(['method' => 'POST', 'route' => ['user.store'], 'id' => 'add_user']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('firstname', 'First Name') !!}
                    {!! Form::text('firstname', '', ['class' => 'form-control'.($errors->has('firstname') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    <p class="text-danger mt-1">{{$errors->first('firstname')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('middlename', 'Middle Name') !!}
                    {!! Form::text('middlename', '', ['class' => 'form-control'.($errors->has('middlename') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    <p class="text-danger mt-1">{{$errors->first('middlename')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('lastname', 'Last Name') !!}
                    {!! Form::text('lastname', '', ['class' => 'form-control'.($errors->has('lastname') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    <p class="text-danger mt-1">{{$errors->first('lastname')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('email', 'Email') !!}
                    {!! Form::email('email', '', ['class' => 'form-control'.($errors->has('email') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    <p class="text-danger mt-1">{{$errors->first('email')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('notify_email', 'Notification Email') !!}
                    {!! Form::email('notify_email', '', ['class' => 'form-control'.($errors->has('notify_email') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    <p class="text-danger mt-1">{{$errors->first('notify_email')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('department_id', 'Department') !!}
                    {!! Form::select('department_id', $departments, NULL, ['class' => 'form-control'.($errors->has('department_id') ? ' is-invalid' : ''), 'form' => 'add_user']) !!}
                    <p class="text-danger mt-1">{{$errors->first('department_id')}}</p>
                </div>
            </div>
            
        </div>

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    <label>Channel Operation Executive{!!$errors->has('coe') ? '<span class="ml-2 badge badge-danger">Required</span>' : ''!!}</label></label>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('coe', 1, false, ['class' => 'custom-control-input', 'id' => 'coe_yes', 'form' => 'add_user']) !!}
                        {!! Form::label('coe_yes', 'YES', ['class' => 'custom-control-label']) !!}
                    </div>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('coe', 0, true, ['class' => 'custom-control-input', 'id' => 'coe_no', 'form' => 'add_user']) !!}
                        {!! Form::label('coe_no', 'NO', ['class' => 'custom-control-label']) !!}
                    </div>
                    <p class="text-danger mt-1">{{$errors->first('coe')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>Group Code{!!$errors->has('group_code') ? '<span class="ml-2 badge badge-danger">Required</span>' : ''!!}</label>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('group_code', 'NKA', true, ['class' => 'custom-control-input', 'id' => 'nka_check', 'form' => 'add_user']) !!}
                        {!! Form::label('nka_check', 'NKA', ['class' => 'custom-control-label']) !!}
                    </div>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('group_code', 'RD', false, ['class' => 'custom-control-input', 'id' => 'rd_check', 'form' => 'add_user']) !!}
                        {!! Form::label('rd_check', 'RD', ['class' => 'custom-control-label']) !!}
                    </div>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('group_code', 'CMD', false, ['class' => 'custom-control-input', 'id' => 'cmd_check', 'form' => 'add_user']) !!}
                        {!! Form::label('cmd_check', 'CMD', ['class' => 'custom-control-label']) !!}
                    </div>
                    <div class="custom-control custom-radio">
                        {!! Form::radio('group_code', 'TOG', false, ['class' => 'custom-control-input', 'id' => 'cmd_check', 'form' => 'add_user']) !!}
                        {!! Form::label('cmd_check', 'TOG', ['class' => 'custom-control-label']) !!}
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <label class="mb-0">USER ROLES{!!$errors->has('roles') ? '<span class="ml-2 badge badge-danger">Required</span>' : ''!!}</label>
                <hr class="m-0 mb-2">
            </div>
            <div class="col-12 row">
                @foreach($roles->chunk(5) as $role_group)
                    <div class="col-lg-3">
                        @foreach($role_group as $role)
                            @if($role->name != 'superadmin' || auth()->user()->hasRole('superadmin'))
                                <div class="custom-control custom-checkbox mb-1">
                                    {!! Form::checkbox('roles[]', $role->name, '', ['class' => 'custom-control-input', 'id' => 'role'.$role->id, 'form' => 'add_user']) !!}
                                    {!! Form::label('role'.$role->id, $role->name, ['class' => 'custom-control-label']) !!}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add User', ['class' => 'btn btn-primary', 'form' => 'add_user']) !!}
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