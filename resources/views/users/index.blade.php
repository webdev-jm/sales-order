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
{!! Form::open(['method' => 'GET', 'route' => ['user.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Users</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
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
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Group Code</th>
                    <th>Role</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <a href="{{route('user.show', $user->id)}}">
                            {{ucwords(strtolower($user->firstname))}}
                        </a>
                    </td>
                    <td>{{ucwords(strtolower($user->lastname))}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->group_code}}</td>
                    <td>{{implode(', ', $user->getRoleNames()->toArray())}}</td>
                    <td class="text-right">
                        @can('user change password')
                        <a href="" title="change password" class="btn-change-pass" data-id="{{$user->id}}"><i class="fas fa-lock text-warning mx-1"></i></a>
                        @endcan
                        {{-- <a href="" title="branches" class="btn-branch-assign" data-id="{{$user->id}}"><i class="fas fa-code-branch text-primary mx-1"></i></a> --}}
                        <a href="#" class="btn-assign-account" data-id="{{$user->id}}" title="user accounts"><i class="fas fa-wrench text-secondary mx-1"></i></a>
                        @can('user edit')
                            <a href="{{route('user.edit', $user->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('user delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$user->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
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

<div class="modal fade" id="modal-delete">
    <div class="modal-dialog">
        <livewire:confirm-delete/>
    </div>
</div>

<div class="modal fade" id="modal-branches">
    <div class="modal-dialog modal-xl">
        <livewire:users.user-branch/>
    </div>
</div>

<div class="modal fade" id="modal-accounts">
    <div class="modal-dialog modal-xl">
        <livewire:users.user-account/>
    </div>
</div>

@can('user change password')
<div class="modal fade" id="modal-change-pass">
    <div class="modal-dialog">
        <livewire:users.user-change-password/>
    </div>
</div>
@endcan
@endsection

@section('js')
<script>
    $(function() {
        $('#btn-upload').on('click', function(e){
            e.preventDefault();
            $('#modal-upload').modal('show');
        });

        $('#upload_form').on('submit', function() {
            $('#modal-upload').modal('hide');
            $('#loadingModal').modal('show');
        });

        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeleteModel', 'User', id);
            $('#modal-delete').modal('show');
        });

        $('body').on('click', '.btn-assign-account', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('userAccount', id);
            $('#modal-accounts').modal('show');
        });

        $('body').on('click', '.btn-branch-assign', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('userBranch', id);
            $('#modal-branches').modal('show');
        });

        @can('user change password')
        $('body').on('click', '.btn-change-pass', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setUser', id);
            $('#modal-change-pass').modal('show');
        });
        @endcan
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection