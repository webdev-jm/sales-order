@extends('adminlte::page')

@section('title')
    Edit Brand
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Edit Brand</h1>
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
{!! Form::open(['method' => 'POST', 'route' => ['brand.update', $brand->id], 'id' => 'edit_brand']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Brand</h3>
        <div class="card-tools">
            
        </div>
    </div>
    <div class="card-body">
        
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    {!! Form::label('brand', 'Brand') !!}
                    {!! Form::text('brand', $brand->brand, ['class' => 'form-control'.($errors->has('brand') ? ' is-invalid' : ''), 'form' => 'edit_brand', 'placeholder' => 'Brand Name']) !!}
                    <small class="text-danger">{{$errors->first('brand')}}</small>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer text-right">
        <button type="submit" class="btn btn-success" form="edit_brand">
            <i class="fa fa-pen-alt mr-1"></i>
            Edit Brand
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