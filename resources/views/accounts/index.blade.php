@extends('adminlte::page')

@section('title')
    Accounts
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Accounts</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('account create')
        <a href="{{route('account.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Account</a>
        <a href="#" class="btn btn-warning" id="btn-upload"><i class="fa fa-upload mr-1"></i>Upload</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['account.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Accounts</h3>
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
                    <th>Short Name</th>
                    <th>Company</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                <tr>
                    <td>
                        <a href="{{route('account.show', encrypt($account->id))}}">
                            {{$account->account_code}}
                        </a>
                    </td>
                    <td>{{$account->account_name}}</td>
                    <td>{{$account->short_name}}</td>
                    <td>{{$account->company->name}}</td>
                    <td class="text-right">
                        <a href="#" class="btn-product-modal" data-id="{{encrypt($account->id)}}"><i class="fas fa-box text-primary"></i></a>
                        @can('shipping address access')
                            <a href="{{route('shipping-address.index', encrypt($account->id))}}" title="shipping addresses"><i class="fas fa-map-marked-alt text-info mx-1"></i></a>
                        @endcan
                        @can('account edit')
                            <a href="{{route('account.edit', encrypt($account->id))}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('account delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{encrypt($account->id)}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$accounts->links()}}
    </div>
</div>

<div class="modal fade" id="modal-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Accounts</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'route' => ['account.upload'], 'id' => 'upload_form', 'enctype' => 'multipart/form-data']) !!}
                {!! Form::close() !!}

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="custom-file">
                                {!! Form::file('upload_file', ['class' => 'custom-file-input'.($errors->has('upload_file') ? ' is-invalid' : ''), 'form' => 'upload_form', 'accept' => '.xlsx, .xls, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']) !!}
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

<div class="modal fade" id="modal-product-assign">
    <div class="modal-dialog modal-lg">
        <livewire:accounts.product-assign/>
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

        $('body').on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setDeleteModel', 'Account', id);
            $('#modal-delete').modal('show');
        });

        $('body').on('click', '.btn-product-modal', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            Livewire.emit('setAccount', id);
            $('#modal-product-assign').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection