@extends('adminlte::page')

@section('title')
    Branches
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Branches</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('branch create')
        <a href="{{route('branch.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Branch</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">List of Branches</h3>
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
                    <th>Account</th>
                    <th>Branch Code</th>
                    <th>Branch Name</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                <tr>
                    <td>{{$branch->account->account_code}} {{$branch->account->short_name}}</td>
                    <td>{{$branch->branch_code}}</td>
                    <td>{{$branch->branch_name}}</td>
                    <td class="text-right">
                        @can('branch edit')
                            <a href="{{route('branch.edit', $branch->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('branch delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$branch->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$branches->links()}}
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
            Livewire.emit('setDeleteModel', 'Branch', id);
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