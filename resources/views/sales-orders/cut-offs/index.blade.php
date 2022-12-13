@extends('adminlte::page')

@section('title')
    Sales Order Cut-offs
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales Order Cut-offs</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('SO cut-off create')
        <a href="{{route('cut-off.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Cut-off</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Cut-offs</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Message</th>
                    <th>{{date('h:m:s a')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cut_offs as $cut_off)
                <tr>
                    <td>{{$cut_off->date}}</td>
                    <td>{{$cut_off->time}}</td>
                    <td>{{$cut_off->message}}</td>
                    <td class="text-right">
                        @can('SO cut-off edit')
                            <a href="{{route('cut-off.edit', $cut_off->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('SO cut-off delete')
                            <a href="#" title="delete" class="btn-delete" data-id="{{$cut_off->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$cut_offs->links()}}
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
