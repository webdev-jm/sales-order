@extends('adminlte::page')

@section('title')
    Invoice Terms
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Invoice Terms</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('invoice term create')
        <a href="{{route('invoice-term.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Invoice Term</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['invoice-term.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Invoice Terms</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 150px;">
                {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default" form="search_form">
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
                    <th>Term Code</th>
                    <th>Description</th>
                    <th>Discount</th>
                    <th>Discount Days</th>
                    <th>Due Days</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice_terms as $invoice_term)
                <tr>
                    <td>{{$invoice_term->term_code}}</td>
                    <td>{{$invoice_term->description}}</td>
                    <td>{{$invoice_term->discount}}</td>
                    <td>{{$invoice_term->discount_days}}</td>
                    <td>{{$invoice_term->due_days}}</td>
                    <td class="text-right">
                        @can('invoice term edit')
                            <a href="{{route('invoice-term.edit', $invoice_term->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                        @endcan
                        @can('invoice term delete')
                            <a href="#" title="delete"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$invoice_terms->links()}}
    </div>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $('#btn-upload').on('click', function(e){
            e.preventDefault();
            $('#modal-upload').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection