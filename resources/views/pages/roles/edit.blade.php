@extends('adminlte::page')

@section('title')
    Roles - Edit
@endsection

@section('css')
<style>
    .card {
        height: 95%;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Roles / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('role.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Edit Role</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['role.update', $role->id], 'id' => 'update_role']) !!}
        {!! Form::close() !!}

        <div class="row">
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('name', 'Role Name') !!}
                    {!! Form::text('name', $role->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_role']) !!}
                    <p class="text-danger mt-1">{{$errors->first('name')}}</p>
                </div>
            </div>

        </div>

        <h4 class="mb-0">Permissions{!!$errors->has('permissions') ? '<span class="badge badge-danger ml-2">Required</span>' : ''!!}</h4>
        <small class="text-muted">(assign permissions)</small>
        <hr class="mt-1">

        <div class="row">
            <div class="col-12">
                <label>PERMISSIONS</label>
                @if($errors->has('permissions'))
                <span class="badge badge-danger ml-1">Required</span>
                @endif
            </div>
            @foreach($permissions as $group => $permission_arr)
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{$group}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($permission_arr as $id => $permission)
                                <div class="col-12">
                                    <div class="custom-control custom-switch">
                                        {!! Form::checkbox('permissions[]', $id, $role->hasPermissionTo(strtolower($permission['name'])), ['class' => 'custom-control-input', 'id' => 'permission'.$id, 'form' => 'update_role']) !!}
                                        {!! Form::label('permission'.$id, ucwords($permission['name']), ['class' => 'custom-control-label']) !!}
                                        <small class="d-block m-0">{{$permission['description']}}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Role', ['class' => 'btn btn-primary', 'form' => 'update_role']) !!}
    </div>
</div>

@endsection

@section('js')
<script>

</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection