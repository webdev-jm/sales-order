
@extends('adminlte::page')

@section('title')
    Salesmen Locations
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Salesmen Locations</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('salesman create')
            <a href="{{route('salesman-location.create')}}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Add Salesman Location
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">List of Salesmen Locations</h3>
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
                    <th>Code</th>
                    <th>Name</th>
                    <th>Province</th>
                    <th>City</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesmen_locations as $location)
                <tr>
                    <td>{{$location->salesman->code ?? ''}}</td>
                    <td>{{$location->salesman->name ?? ''}}</td>
                    <td>{{$location->province}}</td>
                    <td>{{$location->city}}</td>
                    <td class="text-right">
                        @can('salesman location edit')
                            <a href="{{route('salesman-location.edit', $location->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('salesman location delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$location->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$salesmen_locations->links()}}
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
            Livewire.emit('setDeleteModel', 'SalesmanLocation', id);
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