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
                    <th>USER</th>
                    <th>EXPENSE TYPE</th>
                    <th>SUPPORT TYPE</th>
                    <th>TITLE</th>
                    <th>START DATE</th>
                    <th>END DATE</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pafs as $paf)
                    <tr>
                        <td>{{$paf->paf_number}}</td>
                        <td>{{$paf->user->fullName()}}</td>
                        <td>{{$paf->expense_type->expense ?? ''}}</td>
                        <td>{{$paf->support_type->support ?? ''}}</td>
                        <td>{{$paf->title}}</td>
                        <td>{{$paf->start_date}}</td>
                        <td>{{$paf->end_date}}</td>
                        <td>
                            <span class="badge badge-{{$status_arr[$paf->status]}}">{{$paf->status}}</span>
                        </td>
                        <td class="text-right align-middle">
                            <a href="{{route('paf.show', $paf->id)}}" title="details">
                                <i class="fa fa-eye text-primary mx-1"></i>
                            </a>
                            @can('paf edit')
                                @if($paf->status == 'draft')
                                    <a href="{{route('paf.edit', $paf->id)}}" title="edit">
                                        <i class="fa fa-pen-alt text-success mx-1"></i>
                                    </a>
                                @endif
                            @endcan
                            
                        </td>
                    </tr>
                @endforeach
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