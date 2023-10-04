@extends('adminlte::page')

@section('title')
    COE SUBMISSIONS
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>COE SUBMISSIONS</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('channel-operation.report')}}" class="btn btn-primary"><i class="fa fa-chart-bar mr-1"></i>COE REPORT</a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['channel-operation.list'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-lg-6">
                <h3 class="card-title">List of COE Submissions</h3>
            </div>
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="input-group input-group-sm">
                            {!! Form::date('start-date', $start_date ?? '', ['class' => 'form-control', 'form' => 'search_form']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="input-group input-group-sm">
                            {!! Form::date('end-date', $end_date ?? '', ['class' => 'form-control', 'form' => 'search_form']) !!}
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group input-group-sm">
                            {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-sm btn-info btn-block" form="search_form">
                            <i class="fa fa-filter mr-1"></i>
                            FILTER
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Account</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($channel_operations as $channel_operation)
                <tr>
                    <td>{{$channel_operation->branch_login->user->fullName()}}</td>
                    <td>{{$channel_operation->branch_login->branch->account->short_name}}</td>
                    <td>[{{$channel_operation->branch_login->branch->branch_code}}] {{$channel_operation->branch_login->branch->branch_name}}</td>
                    <td>{{$channel_operation->date}}</td>
                    <td>{{$channel_operation->status}}</td>
                    <td class="text-right">
                        <a href="{{route('channel-operation.show', $channel_operation->id)}}" title="view details"><i class="fa fa-eye mx-1 text-primary"></i></a>
                        @can('channel operation print')
                        <a href="{{route('channel-operation.print', $channel_operation->id)}}" title="print"><i class="fas fa-print text-success mx-1"></i></a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$channel_operations->links()}}
    </div>
</div>
@endsection

@section('js')
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection