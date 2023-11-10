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
</div>
@endsection

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['trip.index'], 'id' => 'search_form']) !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Trips</h3>
        <div class="card-tools">
            <div class="row">
                <div class="col-md-3 my-2">
                    <div class="input-group input-group-sm">
                        {!! Form::date('date', $date, ['class' => 'form-control', 'form' => 'search_form']) !!}
                    </div>
                </div>
                <div class="col-md-3 my-2">
                    <div class="input-group input-group-sm">
                        {!! Form::select('user', $users, $user, ['class' => 'form-control form-control-sm', 'form' => 'search_form']) !!}
                    </div>
                </div>
                <div class="col-md-3 my-2">
                    <div class="input-group input-group-sm">
                        {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                    </div>
                </div>
                <div class="col-md-3 my-2">
                    <div class="input-group input-group-sm">
                        {!! Form::submit('Filter', ['class' => 'btn btn-primary btn-sm btn-block', 'form' => 'search_form']) !!}
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
                    <th>Date</th>
                    <th>User</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th class="text-center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                    <tr>
                        <td>{{$trip->trip_number}}</td>
                        @if($trip->source == 'activity-plan')
                            <td>{{$trip->activity_plan_detail->date ?? $trip->schedule->date}}</td>
                            <td>{{$trip->activity_plan_detail->activity_plan->user->fullName()}}</td>
                        @elseif($trip->source == 'schedule')
                            <td>{{$trip->schedule->date}}</td>
                            <td>{{$trip->schedule->user->fullName()}}</td>
                        @endif
                        <td>{{$trip->departure}}</td>
                        <td>{{$trip->arrival}}</td>
                        <td class="text-center">
                            @if(!empty($trip->status) && $trip->status == 'approved')
                                <span class="badge badge-success">{{$trip->status}}</span>
                            @else
                                <span class="badge badge-secondary">for approval</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{route('trip.show', $trip->id)}}" title="view" class="mr-1">
                                <i class="fa fa-eye text-primary"></i>
                            </a>
                            @can('trip print')
                                <a href="{{route('trip.print', $trip->id)}}" title="download">
                                    <i class="fa fa-file-pdf text-danger"></i>
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