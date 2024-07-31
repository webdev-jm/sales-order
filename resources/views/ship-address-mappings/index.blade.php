@extends('adminlte::page')

@section('title')
    Ship Address Mapping
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Ship Address Mapping</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('ship address mapping create')
            <a href="{{route('ship-address-mapping.create')}}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i>
                ADD SHIP ADDRESS MAPPING
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['ship-address-mapping.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Ship Address Mapping</h3>
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
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Shipping Address</th>
                    <th>Reference 1</th>
                    <th>Reference 2</th>
                    <th>Reference 3</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($ship_address_mappings as $mapping)
                    <tr>
                        <td>{{$mapping->account->account_code}}</td>
                        <td>{{$mapping->account->short_name}}</td>
                        <td>[{{$mapping->shipping_address->address_code}}] {{$mapping->shipping_address->ship_to_name}} - {{$mapping->shipping_address->building}}</td>
                        <td>{{$mapping->reference1 ?? '-'}}</td>
                        <td>{{$mapping->reference2 ?? '-'}}</td>
                        <td>{{$mapping->reference3 ?? '-'}}</td>
                        <td class="text-right">
                            @can('ship address mapping edit')
                                <a href="{{route('ship-address-mapping.edit', encrypt($mapping->id))}}" title="edit">
                                    <i class="fas fa-edit text-success mx-1"></i>
                                </a>
                            @endcan
                            @can('ship address mapping delete')
                                <a href="#" title="delete">
                                    <i class="fas fa-trash-alt text-danger mx-1"></i>
                                </a>
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
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection