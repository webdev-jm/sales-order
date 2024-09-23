@extends('adminlte::page')

@section('title')
    Create Brand
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Create Brand</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('brand.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'POST', 'route' => ['brand.store'], 'id' => 'add_brand']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Brand</h3>
        <div class="card-tools">
        </div>
    </div>
    <div class="card-body">
        
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('brand', 'Brand') !!}
                    {!! Form::text('brand', '', ['class' => 'form-control'.($errors->has('brand') ? ' is-invalid' : ''), 'form' => 'add_brand', 'placeholder' => 'Brand Name']) !!}
                    <small class="text-danger">{{$errors->first('brand')}}</small>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer text-right">
        <button type="submit" class="btn btn-primary" form="add_brand">
            <i class="fa fa-plus mr-1"></i>
            Add Brand
        </button>
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection