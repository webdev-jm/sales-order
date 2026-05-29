@extends('adminlte::page')

@section('title')
    Credit Memo Reasons
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>CM Reasons</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('cm reason create')
            <a href="{{route('cm-reason.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add CM Reason</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
        <h3 class="card-title">List of CM Reasons</h3>
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
                        <th>Reason Code</th>
                        <th>Description</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cm_reasons as $reason)
                        <tr>
                            <td>{{$reason->reason_code}}</td>
                            <td>{{$reason->reason_description}}</td>
                            <td class="text-right">
                                @can('cm reason edit')
                                    <a href="{{route('cm-reason.edit', $reason->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                                @endcan
                                @can('cm reason delete')
                                    <a href="#" title="delete" class="btn-delete" data-id="{{$reason->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$cm_reasons->links()}}
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
