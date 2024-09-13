@extends('adminlte::page')

@section('title')
    PAF
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>PAF</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('paf create')
            <a href="{{route('paf.create')}}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i>
                ADD PAF
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of PAF</h3>
        <div class="card-tools">
            
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>PAF NUMBER</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
    </div>
</div>

<div class="modal fade" id="modal-delete">
    <div class="modal-dialog">
        <livewire:confirm-delete/>
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