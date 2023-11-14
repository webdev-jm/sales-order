@extends('adminlte::page')

@section('title')
    Trip Details
@endsection

@section('css')
<style>
    .w100 {
        width: 100px !important;
    }
    
    .trip-icon {
        font-size: 60px !important;
        margin-bottom: 0;
    }
    .middle {
        line-height: 100% !important;
    }
    .w-100 {
        width: 100% !important;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>Trip Details</h1>
        @if(empty($trip->status))
            <button type="button" class="btn btn-secondary">FOR APPROVAL</button>
        @elseif($trip->status == 'approved')
            <button type="button" class="btn btn-success">APPROVED</button>
        @elseif($trip->status == 'rejected')
        <button type="button" class="btn btn-danger">REJECTED</button>
        @endif
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('trip.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">TRIP DETAILS</h3>
                <div class="card-tools">
                    @can('trip print')
                        <a href="{{route('trip.print', $trip->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf mr-1"></i>DOWNLOAD</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 text-center align-middle">
                        {!! DNS1D::getBarcodeSVG($trip->trip_number, 'C39', 1.5, 50, 'black', false); !!}
                        <br>
                        <strong class="text-muted">TRIP CODE</strong>
                        <br>
                        <h3 class="font-weight-bold">{{$trip->trip_number}}</h3>
                    </div>
                    <div class="col-lg-6 text-center">
                        <strong>TRANSPORTATION TYPE</strong>
                        <br>
                        <h3 class="font-weight-bold">
                            @if($trip->transportation_type == 'AIR')
                                <i class="fa fa-plane mr-1"></i>
                            @endif
                            @if($trip->transportation_type == 'LAND')
                                <i class="fa fa-car mr-1"></i>
                            @endif
                            {{$trip->transportation_type}}
                        </h3>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                        <h1 class="font-weight-bold w-100">{{$trip->departure}}</h1>
                    </div>
                    <div class="col-lg-2 text-center align-middle">
                        @if($trip->transportation_type == 'AIR')
                        <h1 class="trip-icon">
                            <i class="fas fa-plane text-primary"></i>
                        </h1>
                        @else
                        <h1 class="trip-icon">
                            <i class="fas fa-car text-primary"></i>
                        </h1>
                        @endif
                    </div>
                    <div class="col-lg-5 d-flex align-items-center text-center font-weight-bold">
                        <h1 class="font-weight-bold w-100">{{$trip->arrival}}</h1>
                    </div>
                </div>

                <hr>
                
                <div class="row">
                    <div class="col-lg-4 text-center">
                        <strong class="text-muted">NAME</strong>
                        <br>
                        @if($trip->source == 'activity-plan')
                            <strong class="text-uppercase text-lg">{{$trip->activity_plan_detail->activity_plan->user->fullName()}}</strong>
                        @elseif($trip->source == 'schedule')
                            <strong class="text-uppercase text-lg">{{$trip->schedule->user->fullName()}}</strong>
                        @endif
                    </div>
                    <div class="col-lg-4 text-center">
                        <strong class="text-muted">DATE</strong>
                        <br>
                        @if($trip->source == 'activity-plan')
                            <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip->activity_plan_detail->date))}}</strong>
                        @elseif($trip->source == 'schedule')
                            <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip->schedule->date))}}</strong>
                        @endif
                    </div>
                    @if(!empty($trip->reference_number))
                        <div class="col-lg-4 text-center">
                            <strong class="text-muted">REFERENCE NUMBER</strong>
                            <br>
                            <strong class="text-uppercase text-lg">{{$trip->reference_number}}</strong>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-footer text-right">
                @if(auth()->user()->can('trip approve') && $trip->status != 'approved' && $trip->status != 'rejected')
                    {!! Form::open(['method' => 'POST', 'route' => ['trip.submit-approve', $trip->id], 'id' => 'approve_trip']) !!}
                        <input type="hidden" name="status" id="status" form="approve_trip">
                    {!! Form::close() !!}
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group text-left">
                                <label>REMARKS</label>
                                <textarea class="form-control" name="remarks" form="approve_trip"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- <a href="{{route('trip.reject', $trip->id)}}" class="btn btn-danger">
                        <i class="fa fa-times-circle mr-1"></i>
                        REJECT
                    </a>
                    <a href="{{route('trip.approve', $trip->id)}}" class="btn btn-success">
                        <i class="fa fa-check mr-1"></i>
                        APPROVE
                    </a> --}}
                    
                    <button class="btn btn-danger" type="button" form="approve_trip" id="btn-reject">
                        <i class="fa fa-times-circle mr-1"></i>
                        REJECT
                    </button>

                    <button class="btn btn-success" type="button" form="approve_trip" id="btn-approve">
                        <i class="fa fa-check-circle mr-1"></i>
                        APPROVE
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">APPROVALS</h3>
            </div>
            <div class="card-body">

                <div class="timeline timeline-inverse">

                    @foreach($approvals as $date => $data)
                        {{-- DATE LABEL --}}
                        <div class="time-label">
                            <span class="bg-primary text-uppercase">
                                {{date('M j Y', strtotime($date))}}
                            </span>
                        </div>
    
                        @foreach($data as $approval)
                            <!-- timeline item -->
                            <div>
                                <i class="fas fa-user bg-{{$status_arr[$approval->status]}}"></i>
    
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i> {{$approval->created_at->diffForHumans()}}</span>
    
                                    <h3 class="timeline-header {{!empty($approval->remarks) ? '' : 'border-0'}}">
                                        <a href="#">{{$approval->user->fullName()}}</a> <span class="mx-2 badge bg-{{$status_arr[$approval->status]}}">{{$approval->status}}</span> the trip request
                                    </h3>
    
                                    @if(!empty($approval->remarks))
                                        <div class="timeline-body">
                                            <label class="mb-0">REMARKS:</label>
                                            <p class="mb-0 ml-2">{{$approval->remarks}}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
    
                    @endforeach
    
                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                </div>
    
                <div class="row mt-2">
                    <div class="col-12">
                        {{$approval_dates->links()}}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        $('body').on('click', '#btn-reject', function(e) {
            e.preventDefault();
            var status = 'rejected';
            $('body').find('#status').val(status);
            $('#'+$(this).attr('form')).submit();
        });

        $('body').on('click', '#btn-approve', function(e) {
            e.preventDefault();
            var status = 'approved';
            $('body').find('#status').val(status);
            $('#'+$(this).attr('form')).submit();
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection