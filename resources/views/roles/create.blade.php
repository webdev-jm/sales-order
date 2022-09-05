@extends('adminlte::page')

@section('title')
    Roles - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Roles / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('role.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">Add Role</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['role.store'], 'id' => 'add_role']) !!}
        {!! Form::close() !!}

        <div class="row">
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('name', 'Role Name') !!}
                    {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_role']) !!}
                    <p class="text-danger mt-1">{{$errors->first('name')}}</p>
                </div>
            </div>

        </div>

        <h4 class="mb-0">Permissions{!!$errors->has('permissions') ? '<span class="badge badge-danger ml-2">Required</span>' : ''!!}</h4>
        <small class="text-muted">(assign permissions)</small>
        <hr class="mt-1">

        <div class="row">
            @foreach($permissions as $permission)
            <div class="col-lg-3">
                <div class="custom-control custom-checkbox mb-3">
                    {!! Form::checkbox('permissions[]', $permission->name, '', ['class' => 'custom-control-input', 'id' => 'permission'.$permission->id, 'form' => 'add_role']) !!}
                    {!! Form::label('permission'.$permission->id, $permission->name, ['class' => 'custom-control-label']) !!}
                </div>
            </div>
            @endforeach
        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Role', ['class' => 'btn btn-primary', 'form' => 'add_role']) !!}
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