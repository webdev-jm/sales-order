@extends('adminlte::page')

@section('title')
    Upload Templates
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Upload Templates</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('upload template create')
            <a href="{{route('upload-template.create')}}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Add Template
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">List of templates</h3>
      <div class="card-tools">
        <div class="input-group input-group-sm" style="width: 150px;">
            <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
            <div class="input-group-append">
                <button type="submit" class="btn btn-default">
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
                    <th>Name</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($upload_templates as $template)
                    <tr>
                        <td>{{$template->name}}</td>
                        <td></td>
                        <td class="text-right">
                            @can('upload template edit')
                                <a href="{{route('upload-template.edit', $template->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                            @endcan
                            @can('upload template delete')
                                <a href="#" title="delete" class="btn-delete" data-id="{{$template->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$upload_templates->links()}}
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