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
        <h1>TRIP DETAILS</h1>
        @if(!empty($trip->status))
            <button type="button" class="btn bg-{{$status_arr[$trip->status]}}">{{strtoupper($trip->status)}}</button>
        @endif
    </div>
    <div class="col-md-6 text-right">
        <a href="{{route('trip.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
    </div>
</div>
@endsection

@section('content')
<div class="row">

    <div class="col-lg-7">
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
                        <h1 class="font-weight-bold w-100">{{$trip->from}}</h1>
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
                        <h1 class="font-weight-bold w-100">{{$trip->to}}</h1>
                    </div>
                </div>

                <hr>
                
                <div class="row">
                    <div class="{{$trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                        <strong class="text-muted">NAME</strong>
                        <br>
                        <strong class="text-uppercase text-lg">{{$trip->user->fullName()}}</strong>
                    </div>
                    <div class="{{$trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                        <strong class="text-muted">DEPARTURE DATE</strong>
                        <br>
                        <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip->departure))}}</strong>
                    </div>
                    @if($trip->trip_type == 'round_trip')
                        <div class="col-lg-3 text-center">
                            <strong class="text-muted">RETURN DATE</strong>
                            <br>
                            <strong class="text-uppercase text-lg">{{date('m/d/Y', strtotime($trip->return))}}</strong>
                        </div>
                    @endif
                    <div class="{{$trip->trip_type == 'round_trip' ? 'col-lg-3' : 'col-lg-4'}} text-center">
                        <strong class="text-muted">TYPE</strong>
                        <br>
                        <strong class="text-uppercase text-lg">{{str_replace('_', ' ', $trip->trip_type)}}</strong>
                    </div>
                        
                </div>

                @if(!empty($trip->attachments->count()))
                    <hr>
                    <label>ATTACHMENTS</label>
                    <div class="row">
                        @foreach($trip->attachments as $attachment)
                            <div class="col-lg-6">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title">{{$attachment->title}}</h3>
                                    </div>
                                    <div class="card-body">
                                        <a href="{{asset('storage/uploads/trip-attachments/'.$trip->id.'/'.$attachment->url)}}" target="_blank">{{$attachment->url}}</a>
                                        <p class="mb-0">{{$attachment->description}}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="card-footer text-right">
                @if($trip->status != 'returned' && $trip->status != 'for revision' && $trip->status != 'rejected by finance' && $trip->status != 'approved by finance')
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

                    {{-- for immediate supervisor --}}
                    @if($trip->source != 'trip-add' && $trip->status == 'submitted')
                        @if(!empty($supervisor_ids) && in_array(auth()->user()->id, $supervisor_ids))
                            <button class="btn btn-warning" type="button" form="approve_trip" id="btn-approval" data-status="for revision">
                                <i class="fa fa-times-circle mr-1"></i>
                                FOR REVISION
                            </button>

                            <button class="btn btn-primary" type="button" form="approve_trip" id="btn-approval" data-status="approved by imm. superior">
                                <i class="fa fa-check-circle mr-1"></i>
                                APPROVE BY IMM. SUPERIOR
                            </button>
                        @endif
                    @endif

                    {{-- for admin --}}
                    @if(($trip->source == 'trip-add' && $trip->status == 'submitted') || ($trip->source != 'trip-add' && $trip->status == 'approved by imm. superior'))
                        @if(!empty($admin) && $admin->id == auth()->user()->id)
                            <div class="row">
                                <div class="col-lg-6 text-left">
                                    <label for="amount">AMOUNT</label>
                                    <input type="number" class="form-control" placeholder="Amount" form="approve_trip">
                                </div>
                            </div>

                            <button class="btn btn-warning" type="button" form="approve_trip" id="btn-approval" data-status="returned">
                                <i class="fa fa-times-circle mr-1"></i>
                                RETURN
                            </button>

                            <button class="btn btn-primary" type="button" form="approve_trip" id="btn-approval" data-status="for approval">
                                <i class="fa fa-check-circle mr-1"></i>
                                FOR APPROVAL
                            </button>
                        @endif
                    @endif

                    {{-- for finance approver --}}
                    @if($trip->status == 'for approval' && auth()->user()->can('trip finance approver'))
                        <button class="btn bg-orange" type="button" form="approve_trip" id="btn-approval" data-status="rejected by finance">
                            <i class="fa fa-times-circle mr-1"></i>
                            REJECT
                        </button>

                        <button class="btn btn-primary" type="button" form="approve_trip" id="btn-approval" data-status="approved by finance">
                            <i class="fa fa-check-circle mr-1"></i>
                            APPROVE
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if((($trip->source == 'trip-add' && $trip->status == 'submitted') || ($trip->source == 'activity-plan' && $trip->status == 'approved by imm. superior')) && auth()->user()->can('trip attachment') && (!empty($admin) && $admin->id == auth()->user()->id))
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">ATTACHMENTS</h3>
                </div>
                <div class="card-body">

                    {!! Form::open(['method' => 'POST', 'route' => ['trip.attachment', $trip->id], 'enctype' => 'multipart/form-data', 'id' => 'trip_attach']) !!}
                    {!! Form::close() !!}

                    <div class="row">

                        <div class="col-lg-12">
                            <div class="form-group">
                                {!! Form::label('attachment_file', 'FILE') !!}
                                {!! Form::file('attachment_file', ['class' => 'form-control'.($errors->has('attachment_file') ? ' is-invalid' : ''), 'accept' => '.pdf, .doc, .docx, .xls, .xlsx, image/*', 'form' => 'trip_attach']) !!}
                                <p class="text-danger">{{$errors->first('trip_attach')}}</p>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                {!! Form::label('title', 'TITLE') !!}
                                {!! Form::text('title', '', ['class' => 'form-control'.($errors->has('title') ? ' is-invalid' : ''), 'placeholder' => 'Title', 'form' => 'trip_attach']) !!}
                                <p class="text-danger">{{$errors->first('title')}}</p>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                {!! Form::label('description', 'DESCRIPTION') !!}
                                {!! Form::textarea('description', '', ['class' => 'form-control'.($errors->has('description') ? ' is-invalid' : ''), 'placeholder' => 'Description', 'rows' => 3, 'form' => 'trip_attach']) !!}
                                <p class="text-danger">{{$errors->first('description')}}</p>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="card-footer text-right">
                    <button class="btn btn-primary" type="submit" form="trip_attach">
                        <i class="fa fa-plus mr-1"></i>
                        ADD ATTACHMENT
                    </button>
                </div>
            </div>
        @endif

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
        $('body').on('click', '#btn-approval', function(e) {
            e.preventDefault();
            var status = $(this).data('status');
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