@extends('adminlte::page')

@section('title')
    Users
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Users</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('user create')
        <a href="{{route('user.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add User</a>
        @endcan
        @can('user upload')
        <a href="#" class="btn btn-warning" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Users</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{$user->firstname}} {{$user->lastname}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{implode(', ', $user->getRoleNames()->toArray())}}</td>
                    <td class="text-right">
                        {{-- <livewire:users.user-account :user_id="$user->id"/> --}}
                        @can('user edit')
                            <a href="{{route('user.edit', $user->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('user delete')
                            <a href="#" title="delete"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$users->links()}}
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Users</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['user.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
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
                </div>

            </div>
            <div class="modal-footer text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Upload', ['class' => 'btn btn-primary', 'form' => 'upload_form']) !!}
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('#btn-upload').on('click', function(e){
            e.preventDefault();
            $('#modal-upload').modal('show');
        });

        $('#btn-upload').on('click', function(e){
            e.preventDefault();
            $('#modal-upload').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection