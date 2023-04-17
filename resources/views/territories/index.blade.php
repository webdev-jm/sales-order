@extends('adminlte::page')

@section('title')
    Territories
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Territories</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('territory create')
        <a href="{{route('territory.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Territory</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['territory.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Districts</h3>
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
                    <th>Territory Code</th>
                    <th>Territory Name</th>
                    <th>District</th>
                    <th>User</th>
                    <th>Branches</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($territories as $territory)
                <tr>
                    <td>{{$territory->territory_code}}</td>
                    <td>{{$territory->territory_name}}</td>
                    <td>{{$territory->district->district_code ?? ''}}</td>
                    <td>{{$territory->user->fullName() ?? ''}}</td>
                    <td>{{$territory->branches()->count()}}</td>
                    <td class="text-right">
                        @can('territory edit')
                            <a href="{{route('territory.edit', $territory->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('territory delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$territory->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$territories->links()}}
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
            Livewire.emit('setDeleteModel', 'District', id);
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