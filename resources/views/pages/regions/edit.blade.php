@extends('adminlte::page')

@section('title')
    Regions - Edit
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Regions / Edit</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('region.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>{{__('Back')}}</a>
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Region</h3>
    </div>
    <div class="card-body">
        {!! Form::open(['method' => 'POST', 'route' => ['region.update', $region->id], 'id' => 'update_region']) !!}
        {!! Form::close() !!}

        <div class="row">

            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('region_name', 'Region Name') !!}
                    {!! Form::text('region_name', $region->region_name, ['class' => 'form-control'.($errors->has('region_name') ? ' is-invalid' : ''), 'form' => 'update_region']) !!}
                    <p class="text-danger">{{$errors->first('region_name')}}</p>
                </div>
            </div>

        </div>

    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Edit Region', ['class' => 'btn btn-primary', 'form' => 'update_region']) !!}
    </div>
</div>
@endsection

@section('js')
<script>
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection