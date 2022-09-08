@extends('adminlte::page')

@section('title')
    Sales People
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Sales People</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('sales person create')
        <a href="{{route('sales-person.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Sales Person</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
      <h3 class="card-title">List of Sales People</h3>
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
                    <th>Code</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales_people as $sales_person)
                <tr>
                    <td>{{$sales_person->user->firstname}} {{$sales_person->user->lastname}}</td>
                    <td>{{$sales_person->code}}</td>
                    <td class="text-right">
                        @can('sales person edit')
                            <a href="{{route('sales-person.edit', $sales_person->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('sales person delete')
                            <a href="#" title="delete"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$sales_people->links()}}
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