@extends('adminlte::page')

@section('title')
    Weekly Activity Reports - Form
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
</style>
@endsection

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>Weekly Activity Reports / Details <span class="badge badge-{{$status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span></h1>
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
        <h3 class="card-title">Weekly Activity Report Form</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm report-table">
            <thead>
                <tr>
                    <th class="w200 text-center align-middle px-0">
                        <img src="{{asset('/assets/images/bevi-logo.png')}}" alt="bevi logo">
                    </th>
                    <th class="text-center align-middle war-title" colspan="10">WEEKLY ACTIVITY REPORT</th>
                    <th class="w300 align-top" colspan="3">
                        DATE SUBMITTED: <br>
                        <p class="text-center mb-0 mt-2">{{$weekly_activity_report->date_submitted}}</p>
                    </th>
                </tr>
                {{-- space --}}
                <tr>
                    <th class="border-0" colspan="12"></th>
                </tr>
            </thead>
            <tbody>
                {{-- header --}}
                    <tr>
                        <th class="war-label">NAME:</th>
                        <td colspan="6" class="px-3">{{$weekly_activity_report->user->fullName()}}</td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="3"></td>
    
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
                        <td class="border-0" colspan="3"></td>
    
                        <th class="war-label">WEEK:</th>
                        <td colspan="3" class="px-3 align-middle">
                            Week {{$weekly_activity_report->week_number}}
                        </td>
                    </tr>
                    <tr>
                        <th class="war-label">AREA VISITED:</th>
                        <td colspan="6" class="px-3">
                            [{{$weekly_activity_report->area->area_code}}] {{$weekly_activity_report->area->area_name}}
                        </td>
    
                        {{-- space --}}
                        <td class="border-0" colspan="7"></td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{-- objectives --}}
                    <tr>
                        <th class="align-middle war-label" colspan="14">I. OBJECTIVE/S</th>
                    </tr>
                    <tr>
                        <td class="px-3" colspan="14">
                            <p class="min-h-100">
                                {{$weekly_activity_report->objectives()->first()->objective}}  
                            </p>
                        </td>
                    </tr>
                {{-- areas --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            II. AREAS
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="2">DATE</th>
                        <th colspan="2">DAY</th>
                        <th colspan="3">AREA COVERED</th>
                        <th colspan="3">IN/OUT BASE</th>
                        <th colspan="4">ACTIVITIES/REMARKS</th>
                    </tr>
                    @if(!empty($weekly_activity_report->areas))
                        @foreach($weekly_activity_report->areas as $area)
                        <tr class="line-row areas">
                            <td colspan="2" class="align-middle text-center">
                                {{$area->date}}
                            </td>
                            <td colspan="2" class="align-middle text-center">
                                {{$area->day}}
                            </td>
                            <td colspan="3" class="align-middle text-center">
                                {{$area->location}}
                            </td>
                            <td colspan="3" class="align-middle text-center">
                                {{$area->in_base}}
                            </td>
                            <td colspan="4">
                                {{$area->remarks}}
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr class="line-row areas">
                        <td colspan="14" class="text-center">NO DATA</td>
                    </tr>
                    @endif
                {{-- Highlights --}}
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
                {{-- collections --}}
                    <tr class="text-center section-header">
                        <th colspan="3">BEGINNING AR</th>
                        <th colspan="4">DUE FOR COLLECTION</th>
                        <th colspan="4">BEGINNING HANGING BALANCE</th>
                        <th colspan="3">TARGET RECONCILIATIONS</th>
                    </tr>
                    <tr class="text-center">
                        <td colspan="3" class="px-3 align-middle">
                            {{$weekly_activity_report->collection->beginning_ar}}
                        </td>
                        <td colspan="4" class="px-3">
                            {{$weekly_activity_report->collection->due_for_collection}}
                        </td>
                        <td colspan="4" class="px-3">
                            {{$weekly_activity_report->collection->beginning_hanging_balance}}
                        </td>
                        <td colspan="3" class="px-3">
                            {{$weekly_activity_report->collection->target_reconciliations}}
                        </td>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="3">WEEK TO DATE</th>
                        <th colspan="4">MONTH TO DATE</th>
                        <th colspan="4">MONTH TARGET</th>
                        <th colspan="3">BALANCE TO SELL</th>
                    </tr>
                    <tr class="text-center">
                        <td colspan="3" class="px-3">
                            {{$weekly_activity_report->collection->week_to_date}}
                        </td>
                        <td colspan="4" class="px-3">
                            {{$weekly_activity_report->collection->month_to_date}}
                        </td>
                        <td colspan="4" class="px-3">
                            {{$weekly_activity_report->collection->month_target}}
                        </td>
                        <td colspan="3" class="px-3">
                            {{$weekly_activity_report->collection->balance_to_sell}}
                        </td>
                    </tr>
                {{-- action plans --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            IV. SALES ACTION PLAN (to achieve sales/collection targets/to accomplish a project):
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="6">ACTION PLAN/S</th>
                        <th colspan="2">TIMETABLE</th>
                        <th colspan="6">PERSON/S RESPONSIBLE</th>
                    </tr>
                    @if(!empty($weekly_activity_report->action_plans))
                        @foreach($weekly_activity_report->action_plans as $action_plan)
                        <tr class="line-row action-plans">
                            <td colspan="6" class="px-3">
                                {{$action_plan->action_plan}}
                            </td>
                            <td colspan="2" class="px-3 text-center">
                                {{$action_plan->time_table}}
                            </td>
                            <td colspan="6" class="px-3 text-center">
                                {{$action_plan->person_responsible}}
                            </td>
                        </tr>
                        @endforeach
                    @else
                    <tr class="line-row action-plans">
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('action_plan[]', '', ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="2" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::date('time_table[]', date('Y-m-d'), ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="6" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('person_responsible[]', '', ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endif
                {{-- activities --}}
                    <tr>
                        <th class="align-middle war-label pr-1" colspan="14">
                            V. ACTIVITIES
                        </th>
                    </tr>
                    <tr class="text-center section-header">
                        <th colspan="4">ACTIVITY</th>
                        <th>NO. OF DAYS (Weekly)</th>
                        <th>NO. OF DAYS (MTD)</th>
                        <th colspan="5">AREA/REMARKS</th>
                        <th>NO. OF DAYS (YTD)</th>
                        <th colspan="3">% to TOTAL WORKING DAYS</th>
                    </tr>
                    @php
                        $total_weekly = 0;
                        $total_mtd = 0;
                        $total_ytd = 0;
                    @endphp
                    @if(!empty($weekly_activity_report->activities))
                        @foreach($weekly_activity_report->activities as $activity)
                            @php
                                $total_weekly += $activity->no_of_days_weekly;
                                $total_mtd += $activity->no_of_days_mtd;
                                $total_ytd += $activity->no_of_days_ytd;
                            @endphp
                            <tr class="line-row activities">
                                <td colspan="4" class="px-3">
                                    {{$activity->activity}}
                                </td>
                                <td class="px-3 text-center">
                                    {{$activity->no_of_days_weekly}}
                                </td>
                                <td class="px-3 text-center">
                                    {{$activity->no_of_days_mtd}}
                                </td>
                                <td colspan="5" class="px-3">
                                    {{$activity->remarks}}
                                </td>
                                <td class="px-3 text-center">
                                    {{$activity->no_of_days_ytd}}
                                </td>
                                <td colspan="3" class="px-3 text-center">
                                    {{$activity->percent_to_total_working_days}}
                                </td>
                            </tr>
                        @endforeach
                    @else
                    <tr class="line-row activities">
                        <td colspan="4" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('activity[]', '', ['class' => 'form-control border-0', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_weekly[]', '', ['class' => 'form-control border-0 text-center days-weekly', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_mtd[]', '', ['class' => 'form-control border-0 text-center days-mtd', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="5" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('activity_remarks[]', '', ['class' => 'form-control border-0 text-center', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::number('no_of_days_ytd[]', '', ['class' => 'form-control border-0 text-center days-ytd', 'form' => 'update_war']) !!}
                            </div>
                        </td>
                        <td colspan="3" class="p-0">
                            <div class="input-group input-group-sm">
                                {!! Form::text('total_working_days[]', '', ['class' => 'form-control border-0 text-center days-percent bg-white', 'form' => 'update_war', 'readonly']) !!}
                                <span class="input-group-prepend align-middle">
                                    <a href="" class="px-2 btn-remove-row"><i class="fa fa-trash-alt text-danger"></i></a>
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endif
                    <tr class="text-center war-label">
                        <th colspan="4" class="">TOTAL</th>
                        <td id="total-weekly" class="px-3 align-middle font-weight-bold">{{$total_weekly}}</td>
                        <td id="total-mtd" class="px-3 align-middle font-weight-bold">{{$total_mtd}}</td>
                        <td colspan="5"></td>
                        <td id="total-ytd" class="px-3 align-middle font-weight-bold">{{$total_ytd}}</td>
                        <td colspan="3"></td>
                    </tr>
                {{-- spacing --}}
                    <tr>
                        <th class="border-0" colspan="14"></th>
                    </tr>
                {{--  --}}
            </tbody>
        </table>
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
    });
</script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
