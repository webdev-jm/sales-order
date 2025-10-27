@extends('adminlte::page')

@section('title')
    RUD
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>RUD</h1>
    </div>
    <div class="col-md-6 text-right">
        @can('cm create')
            <a href="{{route('cm.create')}}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add RUD</a>
        @endcan
    </div>
</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
        <h3 class="card-title">List of RUD</h3>
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
                        <th>Account</th>
                        <th>Invoice</th>
                        <th>SO Number</th>
                        <th>PO Number</th>
                        <th>Reason</th>
                        <th>User</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($credit_memos as $credit_memo)
                        <tr>
                            <td>{{ $credit_memo->account->account_code }}</td>
                            <td>{{ $credit_memo->invoice_number }}</td>
                            <td>{{ $credit_memo->so_number }}</td>
                            <td>{{ $credit_memo->po_number }}</td>
                            <td>{{ $credit_memo->reason->reason_description }}</td>
                            <td>{{ $credit_memo->user->fullName() }}</td>
                            <td>
                                <span class="badge badge-{{ $status_arr[$credit_memo->status] }} text-uppercase">
                                    {{ $credit_memo->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{route('cm.show', $credit_memo->id)}}" title="view details">
                                    <i class="fas fa-eye text-primary mx-1"></i>
                                </a>
                                @if($credit_memo->status == 'draft')
                                    @can('cm edit')
                                        <a href="{{route('cm.edit', $credit_memo->id)}}" title="edit"><i class="fas fa-edit text-success mx-1"></i></a>
                                    @endcan
                                @endif
                                @can('cm delete')
                                    <a href="#" title="delete" class="btn-delete" data-id="{{$credit_memo->id}}"><i class="fas fa-trash-alt text-danger mx-1"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$credit_memos->links()}}
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
