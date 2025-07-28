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
{!! Form::close() !!}

<div class="card">
    <div class="card-header">
        <h3 class="card-title">FILTERS</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 my-2">
                <div class="form-group">
                    {!! Form::label('date_from', 'From:') !!}
                    <div class="input-group input-group-sm">
                        {!! Form::date('date_from', $date_from, ['class' => 'form-control', 'form' => 'search_form']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-2 my-2">
                <div class="form-group">
                    {!! Form::label('date_to', 'To:') !!}
                    <div class="input-group input-group-sm">
                        {!! Form::date('date_to', $date_to, ['class' => 'form-control', 'form' => 'search_form']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3 my-2">
                <div class="form-group">
                    {!! Form::label('search', 'Search:') !!}
                    <div class="input-group input-group-sm">
                        {!! Form::text('search', $search, ['class' => 'form-control float-right', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3 my-2">
                <div class="form-group">
                    {!! Form::label('user', 'User:') !!}
                    <div class="input-group input-group-sm">
                        {!! Form::select('user', $users, $user, ['class' => 'form-control form-control-sm', 'form' => 'search_form']) !!}
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 my-2">
                <div class="form-group">
                    {!! Form::label('company', 'Company:') !!}
                    <div class="input-group input-group-sm">
                        {!! Form::select('company', ['' => 'ALL', 'bevi' => 'BEVI', 'beva' => 'BEVA'], $company, ['class' => 'form-control form-control-sm', 'form' => 'search_form']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        {!! Form::submit('Filter', ['class' => 'btn btn-primary btn-sm', 'form' => 'search_form']) !!}
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List of Trips</h3>
        <div class="card-tools">
            <a href="{{route('trip.export').(!empty($query_string) ? '?'.$query_string : '')}}" class="btn btn-success btn-sm">
                <i class="fa fa-download mr-1"></i>
                EXPORT
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap table-sm">
            <thead>
                <tr>
                    <th>Trip Code</th>
                    @can('trip invoice')
                        <th>Invoice</th>
                    @endcan
                    <th>User</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Departure</th>
                    <th>Return</th>
                    <th>Status</th>
                    <th>Responsible</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                    <tr>
                        <td class="font-weight-bold">{{$trip->trip_number}}</td>
                        @can('trip invoice')
                            <td class="text-center">
                                @if(!empty($trip->invoice_number))
                                    <a href="" class="text-decoration-none text-danger font-bold btn-invoice" data-id="{{$trip->id}}">
                                        {{$trip->invoice_number}}
                                    </a>
                                @else
                                    <a class="btn btn-xs btn-primary btn-invoice" data-id="{{$trip->id}}">
                                        <i class="fa fa-plus fa-sm"></i>
                                        Add invoice
                                    </a>
                                @endif
                            </td>
                        @endcan
                        <td>{{$trip->user->fullName()}}</td>
                        <td class="text-uppercase">{{$trip->from}}</td>
                        <td class="text-uppercase">{{$trip->to}}</td>
                        <td>{{date('Y-m-d (D)', strtotime($trip->departure))}}</td>
                        <td>{{!empty($trip->return) ? date('Y-m-d (D)', strtotime($trip->return)) : '-'}}</td>
                        <td>
                            @if(!empty($trip->status))
                                <span class="badge bg-{{$status_arr[$trip->status]}}">
                                    {{$trip->status}}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($trip->status))
                                @if($trip->status == 'submitted' && !empty($trip->user->department) && strtolower($trip->user->department->department_code) != 'sales')
                                    <span>Admin</span>
                                @else
                                    <span>{{$status_responsible_arr[$trip->status]}}</span>
                                @endif
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

@can('trip invoice')
<div class="modal fade" id="invoive-modal">
    <div class="modal-dialog modal-lg">
        <livewire:trip.invoice/>
    </div>
</div>
@endcan

@endsection

@section('js')
@can('trip invoice')
    <script>
        $(function() {
            
            $('body').on('click', '.btn-invoice', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.emit('setTripId', id);
                $('#invoive-modal').modal('show');
            });
        });
    </script>
@endcan
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection