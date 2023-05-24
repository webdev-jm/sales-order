@extends('adminlte::page')

@section('title')
    Shipping Address
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Shipping Address / [{{$account->account_code}}] {{$account->account_name}}</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('account create')
        <a href="{{route('shipping-address.create', encrypt($account->id))}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Shipping Address</a>
        <a href="#" class="btn btn-warning" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['shipping-address.index', encrypt($account->id)], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Shipping Address</h3>
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
                    <th>Name</th>
                    <th>Building</th>
                    <th>Street</th>
                    <th>City</th>
                    <th>Postal</th>
                    <th>Tin</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipping_addresses as $shipping_address)
                <tr>
                    <td>{{$shipping_address->address_code}}</td>
                    <td>{{$shipping_address->ship_to_name}}</td>
                    <td>{{$shipping_address->building}}</td>
                    <td>{{$shipping_address->street}}</td>
                    <td>{{$shipping_address->city}}</td>
                    <td>{{$shipping_address->postal}}</td>
                    <td>{{$shipping_address->tin}}</td>
                    <td class="text-right">
                        @can('shipping address edit')
                            <a href="{{route('shipping-address.edit', encrypt($shipping_address->id))}}" title="edit">
                                <i class="fas fa-edit text-success mx-1"></i>
                            </a>
                        @endcan
                        {{-- <a href="{{route('sales-order.show', $shipping_address->id)}}" title="view">
                            <i class="fa fa-eye text-primary mx-1"></i>
                        </a> --}}
                        @can('sales order delete')
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
        {{$shipping_addresses->links()}}
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Shipping Addresses</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['shipping-address.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
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