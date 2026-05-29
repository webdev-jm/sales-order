@extends('adminlte::page')

@section('title')
    Salesmen Location - Create
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Salesmen Location - Create</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('salesman.index')}}" class="btn btn-default">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'post', 'route' => ['salesman-location.store'], 'id' => 'add_salesman_location']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Saleman Location</h3>
    </div>
    <div class="card-body">

        <div class="row">

            {{-- Salesman --}}
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('salesman_id', 'Salesman') !!}
                    {!! Form::select('salesman_id', [], NULL, ['class' => 'form-control'.($errors->has('salesman_id') ? ' is-invalid' : ''), 'form' => 'add_salesman_location']) !!}
                    <p class="text-danger">{{$errors->first('salesman_id')}}</p>
                </div>
            </div>

            {{-- Province --}}
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('province', 'Province') !!}
                    {!! Form::text('province', '', ['class' => 'form-control'.($errors->has('province') ? ' is-invalid' : ''), 'form' => 'add_salesman_location']) !!}
                    <p class="text-danger">{{$errors->first('province')}}</p>
                </div>
            </div>

            {{-- City --}}
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('city', 'City') !!}
                    {!! Form::text('city', '', ['class' => 'form-control'.($errors->has('city') ? ' is-invalid' : ''), 'form' => 'add_salesman_location']) !!}
                    <p class="text-danger">{{$errors->first('city')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Add Salesman Location', ['class' => 'btn btn-primary', 'form' => 'add_salesman_location']) !!}
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

        $('#salesman_id').select2({
            ajax: { 
                url: '{{route("salesman.ajax")}}',
                type: "POST",
                dataType: 'json',
                data: function (params) {
                    return {
                        search: params.term // search term
                    };
                },
                processResults: function (response) {
                    console.log(response);
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