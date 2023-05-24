@extends('adminlte::page')

@section('title')
    Shipping Address - Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Shipping Address / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('shipping-address.index', encrypt($shipping_address->account_id))}}" class="btn btn-default"><i class="fa fa-arrow-left mr-2"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Shipping Address</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['shipping-address.update', encrypt($shipping_address->id)], 'id' => 'update_shipping_address']) !!}
        {!! Form::close() !!}

        <div class="row">
            
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('account_id', 'Account') !!}
                    {!! Form::select('account_id', [], $shipping_address->account_id, ['class' => 'form-control'.($errors->has('account_id') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('account_id')}}</p>
                </div>
            </div>

        </div>

        <div class="row">
            
            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('address_code', 'Address Code') !!}
                    {!! Form::text('address_code', $shipping_address->address_code, ['class' => 'form-control'.($errors->has('address_code') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('address_code')}}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('ship_to_name', 'Name') !!}
                    {!! Form::text('ship_to_name', $shipping_address->ship_to_name, ['class' => 'form-control'.($errors->has('ship_to_name') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('ship_to_name')}}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('building', 'Building') !!}
                    {!! Form::text('building', $shipping_address->building, ['class' => 'form-control'.($errors->has('building') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('building')}}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('street', 'Street') !!}
                    {!! Form::text('street', $shipping_address->street, ['class' => 'form-control'.($errors->has('street') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('street')}}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('city', 'City') !!}
                    {!! Form::text('city', $shipping_address->city, ['class' => 'form-control'.($errors->has('city') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('city')}}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('postal', 'Postal') !!}
                    {!! Form::text('postal', $shipping_address->postal, ['class' => 'form-control'.($errors->has('postal') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('postal')}}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-group">
                    {!! Form::label('tin', 'TIN') !!}
                    {!! Form::text('tin', $shipping_address->tin, ['class' => 'form-control'.($errors->has('tin') ? ' is-invalid' : ''), 'form' => 'update_shipping_address']) !!}
                    <p class="text-danger">{{$errors->first('tin')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Shipping Address', ['class' => 'btn btn-primary', 'form' => 'update_shipping_address']) !!}
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

        var user_select = $('#account_id');
        $.ajax({
            type:'GET',
            url: '/account/get-ajax/{{$shipping_address->account_id}}'
        }).then(function(data) {
            console.log(data);
            var option = new Option('['+data.account_code+'] '+data.short_name, data.id, true, true);
            user_select.append(option).trigger('change');

            user_select.trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        });

    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection