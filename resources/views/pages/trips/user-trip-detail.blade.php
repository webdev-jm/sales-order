@extends('adminlte::page')

@section('title')
    Trip Details
@endsection

@section('css')
<style>
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1>TRIP DETAILS</h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('trip.user', encrypt($trip->user_id))}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">TRIP DETAIL</h3>
        <div class="card-tools">
            <a href="" class="btn btn-xs btn-danger">
                <i class="fa fa-download mr-1"></i>
                DOWNLOAD
            </a>
        </div>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-lg-2 text-center">
                {!! DNS2D::getBarcodeSVG(route('trip.user', encrypt( $trip->user_id, 'user-id')), 'QRCODE', 3, 3) !!}
            </div>
            <div class="col-lg-5 text-center align-middle">
                <strong class="text-muted">TRIP CODE</strong>
                <br>
                <h5 class="font-weight-bold">{{$trip->trip_number}}</h5>
            </div>
            <div class="col-lg-5 text-center align-middle">
                <strong class="text-muted">TRANSPORTATION TYPE</strong>
                <br>
                <h5 class="font-weight-bold">{{strtoupper($trip->transportation_type)}}</h5>
            </div>
        </div>

        <hr>

        <div class="row">

            <div class="col-lg-4 col-md-6 border-left mb-2">
                <strong class="text-muted">NAME</strong>
                <br>
                <h5 class="font-weight-bold text-uppercase">{{$trip->user->fullName()}}</h5>
            </div>

            <div class="col-lg-4 col-md-6 border-left mb-2">
                <strong class="text-muted">TRIP TYPE</strong>
                <br>
                <h5 class="font-weight-bold text-uppercase">{{strtoupper(str_replace('_', ' ', $trip->trip_type))}}</h5>
            </div>

            <div class="col-lg-4 col-md-6 border-left mb-2">
                <strong class="text-muted">DEPARTURE</strong>
                <br>
                <h5 class="font-weight-bold text-uppercase">{{$trip->departure}}</h5>
            </div>
            
            @if($trip->type == 'round_trip')
                <div class="col-lg-4 col-md-6 border-left mb-2">
                    <strong class="text-muted">RETURN</strong>
                    <br>
                    <h5 class="font-weight-bold text-uppercase">{{date('m/d/Y', strtotime($trip->return))}}</h5>
                </div>
            @endif

            <div class="col-lg-4 col-md-6 border-left mb-2">
                <strong class="text-muted">PASSENGER/S</strong>
                <br>
                <h5 class="font-weight-bold text-uppercase">{{$trip->passenger}}</h5>
            </div>

            <div class="col-lg-4 col-md-6 col-md-6 border-left mb-2">
                <strong class="text-muted">FROM</strong>
                <br>
                <h5 class="font-weight-bold text-uppercase">{{$trip->from}}</h5>
            </div>

            <div class="col-lg-4 col-md-6 col-md-6 border-left mb-2">
                <strong class="text-muted">TO</strong>
                <br>
                <h5 class="font-weight-bold text-uppercase">{{$trip->to}}</h5>
            </div>

        </div>

        <hr>

        <div class="row">
            <div class="col-12">
                <strong class="text-muted">OBJECTIVE</strong>
                <p class="font-weight-bold text-lg">
                    {{$trip->activity_plan_detail->activity ?? $trip->purpose}}
                </p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-12">
                <strong class="text-muted">AMOUNT</strong>
                <p class="font-weight-bold text-lg">
                    {{!empty($trip->amount) ? number_format($trip->amount, 2) : '-'}}
                </p>
            </div>
        </div>

        <hr>

        @php
            $approval = $trip->approvals()->where('status', 'for approval')->orderBy('created_at', 'ASC')->first();
        @endphp
        @if(!empty($approval))
            <strong class="text-muted">REMARKS</strong>
            <pre class="font-weight-bold text-lg" style="font-family: 'Courier New', monospace;">{{$approval->remarks ?? '-'}}</pre>

            <hr>
        @endif

        <div class="row">
            @php
                $approval  = $trip->approvals()->where('status', 'submitted')->orderBy('created_at', 'DESC')->first();
            @endphp
            @if(!empty($approval))
                <div class="col-lg-3 text-center">
                    <span class="text-muted font-weight-bold">SUBMITTED BY</span>
                    <br>
                    <input type="text" class="border-0 text-center font-weight-bold text-lg" value="{{strtoupper($approval->user->fullName())}}">
                    <br>
                    <small class="font-weight-bold">{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                </div>
            @endif
            @php
                $approval  = $trip->approvals()->where('status', 'approved by imm. superior')->orderBy('created_at', 'DESC')->first();
            @endphp
            @if(!empty($approval))
                <div class="col-lg-3 text-center">
                    <span class="text-muted font-weight-bold">SUPERVISOR</span>
                    <br>
                    <input type="text" class="border-0 text-center font-weight-bold text-lg" value="{{strtoupper($approval->user->fullName() ?? '')}}">
                    <br>
                    <small class="font-weight-bold">{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                </div>
            @endif
            @php
                $approval = $trip->approvals()->where('status', 'for approval')->orderBy('created_at', 'DESC')->first();
            @endphp
            @if(!empty($approval))
                <div class="col-lg-3 text-center">
                    <span class="text-muted font-weight-bold">ADMIN</span>
                    <br>
                    <input type="text" class="border-0 text-center font-weight-bold text-lg" value="{{strtoupper($approval->user->fullName())}}">
                    <br>
                    <small class="font-weight-bold">{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                </div>
            @endif
            @php
                $approval = $trip->approvals()->where('status', 'approved by finance')->orderBy('created_at', 'DESC')->first();
            @endphp
            @if(!empty($approval))
                <div class="col-lg-3 text-center">
                    <span class="text-muted font-weight-bold">FINANCE</span>
                    <br>
                    <input type="text" class="border-0 text-center font-weight-bold text-lg" value="{{strtoupper($approval->user->fullName())}}">
                    <br>
                    <small class="font-weight-bold">{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                </div>
            @endif
        </div>
        
    </div>
    <div class="card-footer">

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