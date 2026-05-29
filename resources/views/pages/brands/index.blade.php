@extends('adminlte::page')

@section('title')
    Brands
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Brands</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('brand.create')}}" class="btn btn-primary">
            <i class="fa fa-plus mr-1"></i>
            ADD BRAND
        </a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['brand.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Branches</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', '', ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
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
                    <th>BRAND NAME</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                    <tr>
                        <td>{{$brand->brand}}</td>
                        <td class="text-right">
                            <a href="{{route('brand.show', $brand->id)}}" title="View details">
                                <i class="fa fa-eye text-primary mx-1"></i>
                            </a>
                            @can('brand edit')
                                <a href="{{route('brand.edit', $brand->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                            @endcan
                            @can('brand delete')
                                <a href="#" title="delete" class="btn-delete" data-id="{{$brand->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$brands->links()}}
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
            Livewire.emit('setDeleteModel', 'Brand', id);
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