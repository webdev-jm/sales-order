@extends('adminlte::page')

@section('title')
    Sales People - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales People / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('sales-people.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Sales Person</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['sales-person.store'], 'id' => 'add_sales_person']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_id', 'User') !!}
                    {!! Form::select('user_id', [], null, ['class' => 'form-control'.($errors->has('user_id') ? ' is-invalid' : ''), 'form' => 'add_sales_person']) !!}
                    <p class="text-danger">{{$errors->first('user_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('account_id', 'Account') !!}
                    {!! Form::select('account_id', [], null, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'add_sales_person']) !!}
                    <p class="text-danger">{{$errors->first('account_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('code', 'Code') !!}
                    {!! Form::text('code', '', ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'add_sales_person']) !!}
                    <p class="text-danger">{{$errors->first('code')}}</p>
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Sales Person', ['class' => 'btn btn-primary', 'form' => 'add_sales_person']) !!}
    </div>
</div>

@endsection

@section('plugins.Select2', true)

@section('js')
<script>
    $(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#user_id').select2({
            ajax: { 
                url: '{{route("user.ajax")}}',
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

    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection