<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Weekly Productivity Report</title>

    <style>
        .text-center {
            text-align: center !important;
        }
        .align-middle {
            vertical-align: middle;
        }
        .align-bottom {
            vertical-align: bottom;
        }
        .title {
            font-size: 20px !important;
        }
        .val {
            font-size: 12px !important;
        }
        .logo {
            width: 60px;
        }
        .bg-gray {
            background-color: rgb(177, 179, 179);
        }
        .bg-dark {
            background-color: rgb(18, 17, 17);
            color: white;
        }
        .bg-warning {
            background-color: rgb(246, 246, 45);
        }
        .bg-success {
            background-color: rgb(45, 246, 55);
        }
        .bg-danger {
            background-color: rgb(246, 85, 45);
        }
        .text-uppercase {
            text-transform: uppercase;
        }
        .text-danger {
            color: rgb(210, 1, 1);
        }

        .mw-300 {
            max-width: 300px;
        }

        /* borders */
        .border-0 {
            border: 0 !important;
        }
        .bt-0 {
            border-top: 0 !important;
        }
        .bb-0 {
            border-bottom: 0 !important;
        }
        .bl-0 {
            border-left: 0 !important;
        }
        .br-0 {
            border-right: 0 !important;
        }

        /* table */
        .table {
            width: 100%;
            margin-bottom: 0.3rem;
            border-collapse: collapse;
        }
        .table thead {
            display: table-header-group;
            vertical-align: top;
        }
        .table tbody {
            display: table-row-group;
            vertical-align: middle;
        }
        .table tr {
            display: table-row;
        }
        .table th, td {
            border: 1.5px solid rgb(16, 16, 16);
            padding: 4px;
            font-size: 11px;
            text-align: left;
        }
        .table-sm td, th {
            padding: 0.3rem;
        }

        .table-sub-menu {
            background-color:rgb(196, 222, 223);
            color: black;
            text-align: center;
            vertical-align: middle;
        }

        .status-badge {
            padding-left: 5px;
            padding-right: 5px;
            padding-top: 3px;
            padding-bottom: 3px;
        }
        
        .status-approved {
            color: white;
            background-color: green;
        }
        .status-rejected {
            color: white;
            background-color: red;
        }
        .status-draft {
            color: white;
            background-color: gray;
        }
        .status-submitted {
            color: white;
            background-color: blue;
        }
    </style>
</head>
<body>
    
    <table class="table table-sm">
        <thead>
            <tr>
                {{-- logo --}}
                <th class="text-center align-middle" rowspan="3">
                    @if($weekly_activity_report->user->group_code == 'RD')
                        <img src="{{public_path('/assets/images/asia.jpg')}}" alt="logo" class="logo">
                    @else
                        <img src="{{public_path('/assets/images/logo.jpg')}}" alt="logo" class="logo">
                    @endif
                </th>
                {{-- title --}}
                <th class="text-center align-middle title" rowspan="3">
                    WEEKLY PRODUCTIVITY REPORT
                </th>
                {{-- date submitted --}}
                <th class="bb-0">
                    DATE SUBMITTED:
                </th>
            </tr>
            <tr>
                <td class="text-center val bt-0 align-middle">
                    {{$weekly_activity_report->date_submitted}}
                </td>
            </tr>
            <tr>
                <td class="text-center bt-0 align-middle">
                    <b class="status-badge status-{{$weekly_activity_report->status}}">{{strtoupper($weekly_activity_report->status)}}</b>
                </td>
            </tr>
        </thead>
    </table>

    <table class="table table-sm">
        <thead>
            <tr>
                <th class="bg-gray">NAME</th>
                <td class="text-uppercase bg-warning">{{$weekly_activity_report->user->fullName()}}</td>

                <td class="border-0"></td>

                <th class="bg-gray">COVERED PERIOD</th>
                <td>
                    {{$weekly_activity_report->date_from}} to {{$weekly_activity_report->date_to}}
                </td>
            </tr>
            <tr>
                <th class="bg-gray">ACCOUNTS VISITED</th>
                <td>
                    @if(!empty($weekly_activity_report->accounts_visited))
                        {{$weekly_activity_report->accounts_visited ?? '-'}}
                    @else
                        {{$weekly_activity_report->area->area_name ?? '-'}}
                    @endif
                </td>

                <td class="border-0"></td>

                <th class="bg-gray">WEEK</th>
                <td>Week {{$weekly_activity_report->week_number}}</td>
            </tr>
        </thead>
    </table>

    <table class="table table-sm">
        @if(!empty($weekly_activity_report->areas))
            <thead>
                <tr>
                    <th class="bg-gray" colspan="5">II. AREAS</th>
                </tr>
            </thead>
            @foreach($weekly_activity_report->areas as $area)
                <tr class="bg-dark">
                    <th class="text-center align-middle">DATE</th>
                    <th class="text-center align-middle">DAY</th>
                    <th class="text-center align-middle">AREA COVERED</th>
                    <th class="text-center align-middle" colspan="2">REMARKS</th>
                </tr>
                <tr>   
                    <td class="text-center align-middle">
                        {{$area->date}}
                    </td>
                    <td class="text-center align-middle">{{strtoupper($area->day)}}</td>
                    <td class="text-center align-middle">{{$area->location}}</td>
                    <td class="align-middle mw-30d0" colspan="2">{{$area->remarks}}</td>
                </tr>

                @if(!empty($area->war_branches->count()))
                    <tr class="table-sub-menu">
                        <th class="align-middle text-center">BRANCHES</th>
                        <th class="align-middle text-center">STATUS</th>
                        <th class="align-middle text-center">PLAN</th>
                        <th class="align-middle text-center">ACTION POINTS</th>
                        <th class="align-middle text-center">RESULTS</th>
                    </tr>
                    @foreach($area->war_branches as $area_branch)
                        @php
                            $schedule = NULL;
                            if(!empty($area_branch->user_branch_schedule_id)) {
                                $schedule = \App\Models\UserBranchSchedule::find($area_branch->user_branch_schedule_id);
                            }

                            if($area_branch->status == 'NOT VISITED') {
                                $area_branch->branch_login_id = NULL;
                            }
                        @endphp
                        <tr>
                            <td class="p-0 align-middle text-left pl-2">
                                @if(!empty($schedule))
                                    <span class="bg-{{$schedule->source == 'request' ? 'warning' : 'success'}}">
                                        {{$schedule->source}}
                                    </span>
                                    <br>
                                @endif
                                {{$area_branch->branch->account->short_name}} [{{$area_branch->branch->branch_code}}] {{$area_branch->branch->branch_name}}
                            </td>
                            <td class="p-0 align-middle text-center">
                                @if(!empty($area_branch->branch_login_id) && !empty($area_branch->user_branch_schedule_id))
                                    <span class="bg-success px-1">
                                        VISITED
                                    </span>
                                @elseif(empty($area_branch->branch_login_id) && !empty($area_branch->user_branch_schedule_id))
                                    <span class="bg-danger px-1">
                                        NOT VISITED
                                    </span>
                                @else
                                    <span class="bg-warning px-1">
                                        DEVIATION
                                    </span>
                                @endif
                            </td>
                            <td class="p-0 align-middle text-center">
                                @if(!empty($schedule))
                                    {{$schedule->objective}}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-0 align-middle text-left">
                                @if(!empty($area_branch->branch_login))
                                    @if(!empty($area_branch->branch_login->operation_process_id))
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                <label>{{$area_branch->branch_login->operation_process->operation_process}}</label>
                                                @php
                                                    $branch_activities = $area_branch->branch_login->login_activities()->whereNotNull('activity_id')->get()
                                                @endphp
                                                <ol>
                                                    @foreach($branch_activities as $activity)
                                                    <li>{{$activity->activity->description}}
                                                        @if(!empty($activity->remarks))
                                                            <ul>
                                                                <li><b>Remarks: </b>{{$activity->remarks}}</li>
                                                            </ul>
                                                        @endif
                                                    </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        </div>
                                    @elseif(!empty($area_branch->branch_login->login_activities()->count()))
                                        <p>{{$area_branch->branch_login->login_activities()->first()->remarks}}</p>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-0 text-center align-middle">
                                {{$area_branch->action_points}}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        @else
            <thead>
                <tr>
                    <td colspan="5" class="text-center">NO DATA</td>
                </tr>
            </thead>
        @endif
    </table>

    <table class="table table-sm">
        <thead>
            <tr>
                <th class="bg-gray">III. HIGHLIGHT(S) of week's field visit</th>
            </tr>
            <tr>
                <td>
                    {{$weekly_activity_report->highlights}}
                </td>
            </tr>
        </thead>
    </table>

    @php
        $approval = $weekly_activity_report->approvals()->orderBy('created_at', 'DESC')->where('status', 'approved')->first();
    @endphp
    @if(!empty($approval))
        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="bg-gray">APPROVER REMARKS</th>
                </tr>
                <tr>
                    <td>
                        {{$approval->remarks}}
                    </td>
                </tr>
            </thead>
        </table>
    @endif

    <table class="table table-sm">
        <tr>
            <th class="bg-gray">SUBMISSION</th>
            <th class="bg-gray">NAME & SIGNATURE OF NSM</th>
        </tr>
        <tr>
            <th class="text-danger">Tuesday of the ff. week</th>
            <td class="bb-0">
                
            </td>
        </tr>
        <tr>
            <th>SUBMIT TO NSM</th>
            <td class="text-uppercase text-center bt-0">
                @if(!empty($approval))
                    {{$approval->user->fullName()}}
                @endif
            </td>
        </tr>
    </table>

</body>
</html>