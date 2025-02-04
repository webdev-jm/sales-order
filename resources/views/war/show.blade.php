@extends('adminlte::page')

@section('title')
    Weekly Productivity Reports - Form
@endsection

@section('css')
<style>
    .w200 {
        width: 200px !important; 
    }
    .w300 {
        width: 300px !important;
    }
    .war-title {
        font-size: 25px;
    }
    .war-label {
        background-color: rgb(202, 202, 202);
    }

    .report-table th, .report-table td {
        border: 1.5px solid black !important;
    }
    .section-header {
        background-color: black;
        color: white;
    }

    .min-h-100 {
        min-height: 100px !important;
    }

    .sub-line-row {
        background-color:rgb(216, 215, 215);
        color: black;
        text-align: center;
    }
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Productivity Reports / Details <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span></h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('war.index')}}" class="btn btn-default"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('war.print-pdf', $weekly_activity_report->id)}}" class="btn btn-primary" target="_blank"><i class="fa fa-print mr-1"></i>Print</a>
    </div>
</div>
@endsection

@section('content')
@if($weekly_activity_report->status == 'submitted')
    {!! Form::open(['method' => 'POST', 'route' => ['war.approval', $weekly_activity_report->id], 'id' => 'war_approval']) !!}
    {!! Form::hidden('status', $weekly_activity_report->status, ['form' => 'war_approval', 'id' => 'status']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Approval</h3>
            <div class="card-tools">
                @if((in_array(auth()->user()->id, $supervisor_ids) && auth()->user()->can('war approve')) || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin'))
                    {!! Form::submit('Approve', ['class' => 'btn btn-success btn-sm btn-approval', 'form' => 'war_approval']) !!}
                    {!! Form::submit('Reject', ['class' => 'btn btn-danger btn-sm btn-approval', 'form' => 'war_approval']) !!}
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                {!! Form::label('remarks', 'Remarks') !!}
                {!! Form::textarea('remarks', '', ['class' => 'form-control'.($errors->has('remarks') ? ' is-invalid' : ''), 'form' => 'war_approval', 'rows' => 3]) !!}
                @if($errors->has('remarks'))
                <p class="text-danger">{{$errors->first('remarks')}}</p>
                @endif
            </div>
        </div>
    </div>
@elseif($weekly_activity_report->status != 'draft')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Approval History</h3>
        </div>
        <div class="card-body p-0 table-reponsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekly_activity_report->approvals as $approval)
                    <tr>
                        <td>{{$approval->user->fullName()}}</td>
                        <td>
                            <span class="badge badge-{{$status_arr[$approval->status]}}">{{$approval->status}}</span>
                        </td>
                        <td>{{$approval->remarks}}</td>
                        <td>{{$approval->created_at->diffForHumans()}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Weekly Productivity Report Form</h3>
    </div>
    <div class="card-body table-responsive p-0">

        <!-- header -->
        <table class="table table-bordered table-sm report-table mb-2">
            <thead>
                <tr>
                    <th class="w200 text-center align-middle px-0">
                        <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                    </th>
                    <th class="text-center align-middle war-title" colspan="10">WEEKLY PRODUCTIVITY REPORT</th>
                    <th class="w300 align-top" colspan="3">
                        DATE SUBMITTED: <br>
                        <p class="text-center mb-0 mt-2">{{$weekly_activity_report->date_submitted}}</p>
                    </th>
                </tr>
                {{-- space --}}
                <tr>
                    <td style="border: 0 !important;" colspan="14"></td>
                </tr>
            </thead>
            <tbody>
                {{-- header --}}
                <tr>
                    <th class="war-label">NAME:</th>
                    <td colspan="6" class="px-3">{{$weekly_activity_report->user->fullName()}}</td>

                    {{-- space --}}
                    <td style="border: 0 !important;" colspan="3"></td>

                    <th class="war-label">DATE:</th>
                    <td class="px-3 align-middle">
                        {{$weekly_activity_report->date_from}}
                    </td>
                    <td class="text-center">to</td>
                    <td class="px-3 align-middle">
                        {{$weekly_activity_report->date_to}}
                    </td>
                </tr>
                <tr>
                    <th class="war-label">AREA:</th>
                    <td colspan="6" class="px-3 align-middle">
                        [{{$weekly_activity_report->area->area_code}}] {{$weekly_activity_report->area->area_name}}
                    </td>

                    {{-- space --}}
                    <td style="border: 0 !important;" colspan="3"></td>

                    <th class="war-label">WEEK:</th>
                    <td colspan="3" class="px-3 align-middle">
                        Week {{$weekly_activity_report->week_number}}
                    </td>
                </tr>
                {{-- space --}}
                <tr>
                    <td style="border: 0 !important;" colspan="14"></td>
                </tr>
                {{-- objectives --}}
                <tr>
                    <th class="align-middle war-label" colspan="14">I. OBJECTIVE/S</th>
                </tr>
                <tr>
                    <td class="px-3" colspan="14">
                        <p class="min-h-100">
                            {{$weekly_activity_report->objectives}}  
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- areas -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm report-table">
                <thead>
                    <tr>
                        <th class="align-middle war-label" colspan="14">II. AREAS</th>
                    </tr>
                </thead>
                @if(!empty($weekly_activity_report->areas))
                    @foreach($weekly_activity_report->areas as $area)
                        <thead>
                            <tr class="text-center section-header">
                                <th>DATE</th>
                                <th>DAY</th>
                                <th>AREA COVERED</th>
                                <th colspan="2">REMARKS</th>
                            </tr>
                            <tr class="line-row areas text-center">
                                <td class="p-0 align-middle">
                                    {{$area->date}}
                                </td>
                                <td class="p-0 align-middle">
                                    {{$area->day}}
                                </td>
                                <td class="p-0 align-middle">
                                    {{$area->location}}
                                </td>
                                <td class="p-0 align-middle text-center" colspan="2">
                                    <span>
                                        {{$area->remarks}}
                                    </span>
                                    <a href="" class="mx-0 btn btn-xs btn-info btn-area-modal float-right" data-date="{{$area->date}}">
                                        <i class="fa fa-info-circle"></i>
                                        VIEW ACTIVITIES
                                    </a>
                                </td>
                            </tr>
                        </thead>
                        @if(!empty($area->war_branches->count()))
                            <tbody>
                                <tr class="sub-line-row">
                                    <th>BRANCHES</th>
                                    <th>STATUS</th>
                                    <th>PLOTTED ACTIVITIES</th>
                                    <th>ACTUAL ACTIVITIES</th>
                                    <th>ACTION POINTS</th>
                                </tr>
                                @foreach($area->war_branches as $area_branch)
                                <tr class="text-center">
                                        <td class="p-0 align-middle text-left pl-2">
                                            {{$area_branch->branch->account->short_name}} [{{$area_branch->branch->branch_code}}] {{$area_branch->branch->branch_name}}
                                        </td>
                                        <td class="p-0 align-middle">
                                            <span class="bg-{{$area_status_arr[$area_branch->status]}} px-1">
                                                {{$area_branch->status}}
                                            </span>
                                        </td>
                                        <td class="p-0 align-middle">
                                            @php
                                                $schedule = \App\Models\UserBranchSchedule::find($area_branch->user_branch_schedule_id);
                                            @endphp
                                            @if(!empty($schedule))
                                                {{$schedule->objective}}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="p-0 align-middle">
                                            @if(!empty($area_branch->branch_login))
                                                <p class="mb-0">
                                                    <a href="#" data-toggle="tooltip" data-placement="right" title="View Details" class="btn-show-details" data-id="{{$area_branch->branch_login_id}}">
                                                        <i class="fa fa-info-circle text-primary mr-2"></i>
                                                    </a>
                                                    {{$area_branch->branch_login->latitude}}, {{$area_branch->branch_login->longitude}}
                                                </p>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="p-0">
                                            {{$area_branch->action_points}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            
                        @endif
                    @endforeach
                @endif
            </table>
        </div>

        {{-- Highlights --}}
        <table class="table table-bordered table-sm report-table">
            <tbody>
                <tr>
                    <th class="align-middle war-label" colspan="14">III. Highlight(s) of week’s field visit (use 2nd page for more highlights when necessary):</th>
                </tr>
                <tr>
                    <td class="px-3" colspan="14">
                        <p class="min-h-100">
                            {{$weekly_activity_report->highlights}}
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

<div class="modal fade" id="area-activity-modal">
    <div class="modal-dialog modal-lg">
        <livewire:war.war-area-detail :user_id="$weekly_activity_report->user->id"/>
    </div>
</div>

<div class="modal fade" id="detail-modal">
    <div class="modal-dialog modal-lg">
        <livewire:reports.mcp.login-detail/>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function() {
        // set status
        $('body').on('click', '.btn-approval', function(e) {
            e.preventDefault();
            var val = $(this).val();
            var status = 'submitted';
            if(val == 'Approve') {
                status = 'approved';
            } else {
                status = 'rejected';
            }

            $('#status').val(status);
            $('#'+$(this).attr('form')).submit();
        });

        // branch login details
        $('body').on('click', '.btn-area-modal', function(e) {
            e.preventDefault();
            var date = $(this).data('date');
            Livewire.emit('setDate', date);
            $('#area-activity-modal').modal('show');
        });

        $('[data-toggle="tooltip"]').tooltip();

        $('body').on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });

        $('body').on('click', '.btn-show-details', function(e) {
            e.preventDefault();
            
            $('#detail-modal').modal('show');
        });
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
