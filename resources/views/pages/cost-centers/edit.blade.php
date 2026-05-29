@extends('adminlte::page')

@section('title')
    Cost Centers - Add
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Cost Centers / Add</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('cost-center.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Cost Center</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['cost-center.update', $cost_center->id], 'id' => 'update_cost_center']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('company_id', 'Company') !!}
                    {!! Form::select('company_id', $companies, $cost_center->company_id, ['class' => 'form-control'.($errors->has('company_id') ? ' is-invalid' : ''), 'form' => 'update_cost_center']) !!}
                    <p class="text-danger">{{$errors->first('company_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_id', 'User') !!}
                    {!! Form::select('user_id', [], $cost_center->user_id, ['class' => 'form-control'.($errors->has('user_id') ? ' is-invalid' : ''), 'form' => 'update_cost_center']) !!}
                    <p class="text-danger">{{$errors->first('user_id')}}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('cost_center', 'Cost Center') !!}
                    {!! Form::number('cost_center', $cost_center->cost_center, ['class' => 'form-control'.($errors->has('cost_center') ? ' is-invalid' : ''), 'form' => 'update_cost_center']) !!}
                    <p class="text-danger">{{$errors->first('cost_center')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Cost Center', ['class' => 'btn btn-primary', 'form' => 'update_cost_center']) !!}
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
        
        var user_select = $('#user_id');
        $.ajax({
            type:'GET',
            url: '/user/get-ajax/{{$cost_center->user_id}}'
        }).then(function(data) {
            console.log(data);
            var option = new Option(data.firstname+' '+data.lastname, data.id, true, true);
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