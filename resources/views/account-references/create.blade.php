@extends('adminlte::page')

@section('title')
    Account Product References - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Account Product References / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('account-reference.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-2"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Account Product References</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['account-reference.store'], 'id' => 'add_account_reference']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_id', 'Account') !!}
                    {!! Form::select('account_id', [], null, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'add_account_reference']) !!}
                    <p class="text-danger">{{$errors->first('account_id')}}</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('product_id', 'Product') !!}
                    {!! Form::select('product_id', [], null, ['class' => 'form-control'.($errors->has('product_id') ? ' is-invalid' : ''), 'form' => 'add_account_reference']) !!}
                    <p class="text-danger">{{$errors->first('product_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_reference', 'Reference') !!}
                    {!! Form::text('account_reference', '', ['class' => 'form-control'.($errors->has('account_reference') ? ' is-invalid' : ''), 'form' => 'add_account_reference']) !!}
                    <p class="text-danger">{{$errors->first('account_reference')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('description', 'Description') !!}
                    {!! Form::text('description', '', ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'form' => 'add_account_reference']) !!}
                    <p class="text-danger">{{$errors->first('description')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Account Product Reference', ['class' => 'btn btn-primary', 'form' => 'add_account_reference']) !!}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#account_id').select2({
            ajax: { 
                url: '{{route("account.ajax")}}',
                type: "POST",
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });

        $('#product_id').select2({
            ajax: {
                url: '{{route("product.ajax")}}',
                type: "POST",
                dataType: 'json',
                delay: 50,
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection