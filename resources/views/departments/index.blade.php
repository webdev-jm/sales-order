@extends('adminlte::page')

@section('title')
    Departments
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Department</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('department create')
        <a href="{{route('department.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Department</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['department.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Departments</h3>
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
                    <th>Code</th>
                    <th>Department Name</th>
                    <th>Head</th>
                    <th>Admin</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td>{{$department->department_code}}</td>
                        <td>{{$department->department_name}}</td>
                        <td>{{$department->department_head->fullName() ?? '-'}}</td>
                        <td>{{$department->department_admin->fullName() ?? '-'}}</td>
                        <td class="text-right">
                            <a href="{{route('department.show', $department->id)}}" title="view">
                                <i class="fas fa-eye mx-1"></i>
                            </a>
                            @can('department edit')
                                <a href="{{route('department.edit', $department->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                            @endcan
                            @can('department delete')
                                <a href="#" title="delete" class="btn-delete" data-id="{{$department->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
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

@endsection

@section('js')
<script>
    $(function() {

        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeleteModel', 'Discount', id);
            $('#modal-delete').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection