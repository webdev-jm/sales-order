@extends('adminlte::page')

@section('title')
    Trips
@endsection

@section('css')
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Trips</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('trip.create')}}" class="btn btn-primary">
            <i class="fa fa-plus mr-1"></i>
            ADD TRIP
        </a>
    </div>
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['trip.index'], 'id' => 'search_form']) !!}

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-lg-6">
                <h3 class="card-title">List of Trips</h3>
            </div>
            <div class="col-lg-6">
                <div class="row">
                    <div class="{{auth()->user()->can('trip finance approver') ? 'col-md-3' : 'col-md-4'}} my-2">
                        <div class="input-group input-group-sm">
                            {!! Form::date('date', $date, ['class' => 'form-control', 'form' => 'search_form']) !!}
                        </div>
                    </div>
                    @can('trip finance approver')
                        <div class="col-md-3 my-2">
                            <div class="input-group input-group-sm">
                                {!! Form::select('user', $users, $user, ['class' => 'form-control form-control-sm', 'form' => 'search_form']) !!}
                            </div>
                        </div>
                    @endcan
                    <div class="{{auth()->user()->can('trip finance approver') ? 'col-md-3' : 'col-md-4'}} my-2">
                        <div class="input-group input-group-sm">
                            {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                        </div>
                    </div>
                    <div class="{{auth()->user()->can('trip finance approver') ? 'col-md-3' : 'col-md-4'}} my-2">
                        <div class="input-group input-group-sm">
                            {!! Form::submit('Filter', ['class' => 'btn btn-primary btn-sm btn-block', 'form' => 'search_form']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Trip Code</th>
                    <th>User</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Departure</th>
                    <th>Return</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                    <tr>
                        <td class="font-weight-bold">{{$trip->trip_number}}</td>
                        <td>{{$trip->user->fullName()}}</td>
                        <td class="text-uppercase">{{$trip->from}}</td>
                        <td class="text-uppercase">{{$trip->to}}</td>
                        <td>{{$trip->departure}}</td>
                        <td>{{$trip->return ?? '-'}}</td>
                        <td>
                            @if(!empty($trip->status))
                                <span class="badge bg-{{$status_arr[$trip->status]}}">
                                    {{$trip->status}}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(empty($trip->activity_plan_detail_id))
                                <span class="badge badge-info">{{$trip->source == 'trip-add' ? 'manual add' : 'activity plan'}} no mcp tagged</span>
                            @else
                                <span class="badge badge-success">{{$trip->source == 'trip-add' ? 'manual add' : 'activity plan'}} with mcp tagged</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{route('trip.show', $trip->id)}}" title="view" class=" btn btn-xs btn-primary mr-1">
                                <i class="fa fa-eye"></i>
                            </a>
                            @can('trip edit')
                                @if(($trip->status == 'for revision' || $trip->status == 'returned' || $trip->status == 'draft') && auth()->user()->id == $trip->user_id)
                                    <a href="{{route('trip.edit', $trip->id)}}" class="btn btn-xs btn-success mr-1">
                                        <i class="fa fa-pen-alt"></i>
                                    </a>
                                @endif
                            @endcan
                            @can('trip print')
                                <a href="{{route('trip.print', $trip->id)}}" title="download" class="btn btn-xs btn-danger">
                                    <i class="fa fa-file-pdf"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{$trips->links()}}
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