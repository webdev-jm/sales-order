<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MCP Reports</title>

    <style>
        .page-break {
            page-break-after: always;
        }
        
        .text-center {
            text-align: center !important;
        }
        .text-left {
            text-align: left !important;
        }
        .text-right {
            text-align: right !important;
        }
        .float-right {
            float: right;
        }
        .text-danger {
            color: rgb(233, 29, 29);
        }
        .mb-3 {
            margin-bottom: 15px;
        }
        .ml-3 {
            margin-left: 15px;
        }
        .mt-0 {
            margin-top: 0;
        }
        .mb-0 {
            margin-bottom: 0;
        }
        .pb-0 {
            padding-bottom: 0;
        }
        .objective {
            border: 1px solid black;
            padding: 1 10 15 10;
        }
        .bg-warning {
            background-color: rgb(240, 240, 140);
        }
        .font-weight-bold {
            font-weight: bold;
        }
        .bg-navy {
            background-color: #001f3f!important;
            color: rgb(255, 255, 255);
        }
        .bg-secondary {
            background-color: #6c757d!important;
            color: rgb(255, 255, 255);
        }
        .mw-50 {
            min-width: 50px;
        }
        .mw-100 {
            min-width: 100px;
        }
        .text-muted {
            font-weight: 500;
            color: #454545
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
        
        /* sizes */
        .w200 {
            width: 200px;
        }
        .logo-img {
            width: 50px;
            margin-left: 10px;
        }
        .title {
            font-size: 25px;
            margin-left: 150px;
        }
        /* spacing */
        .mt-2 {
            margin-top: 10px;
        }
        .m-0 {
            margin: 0;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            margin-right: -7.5px;
            margin-left: -7.5px;
        }
        .col-2 {
            /* position: relative; */
            display: inline;
            flex: 0 0 17%;
            width: 17%;
            padding-right: 7.5px;
            padding-left: 7.5px;
        }
        .col-8 {
            /* position: relative; */
            display: inline;
            text-align: center;
            flex: 0 0 66%;
            width: 66%;
            padding-right: 7.5px;
            padding-left: 7.5px;
        }

        .badge {
            display: inline-block;
            padding: 0.12em 0.2em;
            font-size: 80%;
            font-weight: 800;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .badge-info {
            color: #fff;
            background: #17a2b8;
        }
        .badge-success {
            color: #fff;
            background-color: #28a745;
        }
        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }
        .badge-primary {
            color: #fff;
            background-color: #007bff;
        }
        .badge-danger {
            color: #fff;
            background-color: #dc3545;
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
    </style>
</head>
<body>

    {{-- ACTIVITY PLANS --}}
    @foreach($activity_plans as $activity_plan)
        @php
            $position = [];
            $organizations = $activity_plan->user->organizations;
            if(!empty($organizations)) {
                foreach($organizations as $organization) {
                    $position[] = $organization->job_title->job_title;
                }
            }

            $last_day = date('t', strtotime($activity_plan->year.'-'.$activity_plan->month.'-01'));
            $lines = [];
            for($i = 1; $i <= (int)$last_day; $i++) {
                $date = $activity_plan->year.'-'.$activity_plan->month.'-'.($i < 10 ? '0'.$i : $i);
                $day = date('D', strtotime($date));
                $class = '';
                if($day == 'Sun') {
                    $class = 'bg-navy';
                } else if($day == 'Sat') {
                    $class = 'bg-secondary';
                }

                // check details
                $details = $activity_plan->details()->where('date', $date)
                ->get();
                $data = [];
                if(!empty($details)) {
                    foreach($details as $detail) {
                        $branch_name = '';
                        $account_name = '';
                        if(!empty($detail->branch_id)) {
                            $branch_name = $detail->branch->branch_code.' - '.$detail->branch->branch_name;
                            $account_name = $detail->branch->account->short_name;
                        }

                        $data[] = [
                            'location' => $detail->exact_location,
                            'account_name' => $account_name,
                            'branch_name' => $branch_name,
                            'purpose' => $detail->activity,
                            'work_with' => !empty($detail->user_id) ? $detail->user->fullName() : ''
                        ];
                    }
                } else {
                    $data[] = [
                        'location' => '',
                        'account_name' => '',
                        'branch_name' => '',
                        'purpose' => '',
                        'work_with' => ''
                    ];
                }

                $lines[$date] = [
                    'day' => $day,
                    'class' => $class,
                    'lines' => $data
                ];
            }
        @endphp

        <div class="mb-3">
            <label>
                <span class="font-weight-bold">ACTIVITY PLAN FOR THE MONTH OF</span> <span class="text-uppercase text-danger">{{date('F Y', strtotime($activity_plan->year.'-'.$activity_plan->month.'-01'))}}</span>
            </label>
            <br>
            <label class="text-uppercase">
                <span class="font-weight-bold">NAME:</span> {{$activity_plan->user->fullName()}}
            </label>
            <br>
            @if(!empty($position))
            <label class="text-uppercase">
                <span class="font-weight-bold">POSITION:</span> {{implode(', ', $position)}}
            </label>
            @endif
            <br>
            <label class="text-uppercase">
                <span class="font-weight-bold">STATUS:</span> <span class="badge badge-{{$activity_plan_status_arr[$activity_plan->status]}}">{{$activity_plan->status}}</span>
            </label>
        </div>

        <div class="mb-3">
            <label class="font-weight-bold">OBJECTIVE FOR THE MONTH</label>
            <p class="objective mt-0">{{$activity_plan->objectives}}</p>
        </div>

        <table class="table table-sm">
            <thead>
                <tr class="bg-warning">
                    <th class="text-center">DAY</th>
                    <th class="text-center">DATE</th>
                    <th class="text-center">EXACT LOCATION</th>
                    <th class="text-center">ACCOUNT</th>
                    <th class="text-center">PURPOSE / ACTIVITY</th>
                    <th class="text-center">WORK WITH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $date => $data)
                    @foreach($data['lines'] as $line)
                    <tr class="{{$data['class']}}">
                        <td class="font-weight-bold">{{$data['day']}}</td>
                        <td class="mw-50 font-weight-bold">{{date('M d', strtotime($date))}}</td>
                        <td>{{$line['location']}}</td>
                        <td>
                            {{$line['branch_name']}}
                            <br>
                            @if(!empty($line['account_name']))
                                <span class="text-muted">[{{$line['account_name']}}]</span>
                            @endif
                        </td>
                        <td class="text-left">{{$line['purpose']}}</td>
                        <td class="mw-100">{{$line['work_with']}}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="page-break"></div>
    @endforeach

    {{-- WEEKLY ACTIVITY REPORT --}}
    @foreach($weekly_activity_reports as $key => $weekly_activity_report)
        @if($key !== 0)
        <div class="page-break"></div>
        @endif

        <div class="text-left">
            <label>
                <span class="font-weight-bold">STATUS:</span> <span class="badge text-uppercase badge-{{$war_status_arr[$weekly_activity_report->status]}}">{{$weekly_activity_report->status}}</span>
            </label>
        </div>

        <table class="table table-sm">
            <thead>
                <tr>
                    {{-- logo --}}
                    <th class="text-center" rowspan="2">
                        <img src="{{public_path('/assets/images/bevi-logo.png')}}" alt="logo" class="logo">
                    </th>
                    {{-- title --}}
                    <th class="text-center align-middle title" rowspan="2">
                        WEEKLY ACTIVITY REPORT
                    </th>
                    {{-- date submitted --}}
                    <th class="bb-0">
                        DATE SUBMITTED:
                    </th>
                </tr>
                <tr>
                    <td class="text-center val bt-0">
                        {{$weekly_activity_report->date_submitted}}
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

                    <th class="bg-gray">DATE</th>
                    <td>
                        {{$weekly_activity_report->date_from}} to {{$weekly_activity_report->date_to}}
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray">AREA</th>
                    <td>[{{$weekly_activity_report->area->area_code}}] {{$weekly_activity_report->area->area_name}}</td>

                    <td class="border-0"></td>

                    <th class="bg-gray">WEEK</th>
                    <td>Week {{$weekly_activity_report->week_number}}</td>
                </tr>
                <tr>
                    <th class="bg-gray">AREA Visited</th>
                    <td>[{{$weekly_activity_report->area->area_code}}] {{$weekly_activity_report->area->area_name}}</td>
                    <td class="border-0" colspan="3"></td>
                </tr>
            </thead>
        </table>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="bg-gray">I. OBJECTIVE/S</th>
                </tr>
                <tr>
                    <td>
                        {{$weekly_activity_report->objectives()->first()->objective}}
                    </td>
                </tr>
            </thead>
        </table>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="bg-gray" colspan="5">II. AREAS</th>
                </tr>
                <tr class="bg-dark">
                    <th class="text-center align-middle">DATE</th>
                    <th class="text-center align-middle">DAY</th>
                    <th class="text-center align-middle">AREA COVERED</th>
                    <th class="text-center align-middle">IN/OUT BASE</th>
                    <th class="text-center align-middle">ACTIVITIES/REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($weekly_activity_report->areas))
                    @foreach($weekly_activity_report->areas as $area)
                        <tr>
                            <td class="text-center align-middle">
                                {{$area->date}}
                            </td>
                            <td class="text-center align-middle">{{$area->day}}</td>
                            <td class="text-center align-middle">{{$area->location}}</td>
                            <td class="text-center align-middle">{{$area->in_base}}</td>
                            <td class="align-middle mw-300">{{$area->remarks}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center">NO DATA</td>
                    </tr>
                @endif
            </tbody>
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

        <table class="table table-sm">
            <thead>
                <tr class="bg-dark">
                    <th class="text-center">BEGINNING AR</th>
                    <th class="text-center">DUE FOR COLLECTION</th>
                    <th class="text-center">BEGINNING HANGING BALANCE</th>
                    <th class="text-center">TARGET RECONCILIATIONS</th>
                </tr>
                <tr>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->beginning_ar)}}</td>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->due_for_collection)}}</td>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->beginning_hanging_balance)}}</td>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->target_reconciliations)}}</td>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-dark">
                    <th class="text-center">WEEK TO DATE</th>
                    <th class="text-center">MONTH TO DATE</th>
                    <th class="text-center">MONTH TARGET</th>
                    <th class="text-center">BALANCE TO SELL</th>
                </tr>
                <tr>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->week_to_date)}}</td>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->month_to_date)}}</td>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->month_target)}}</td>
                    <td class="text-center">{{number_format($weekly_activity_report->collection->balance_to_sell)}}</td>
                </tr>
            </tbody>
        </table>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="bg-gray" colspan="3">IV. SALES Action Plans (to achieve sales/collection targets/to accomplish a project):</th>
                </tr>
                <tr class="bg-dark">
                    <th class="text-center">ACTION PLAN/S</th>
                    <th class="text-center">TIMETABLE</th>
                    <th class="text-center">PERSON/S RESPONSIBLE</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($weekly_activity_report->action_plans))
                    @foreach($weekly_activity_report->action_plans as $action_plan)
                    <tr>
                        <td>{{$action_plan->action_plan}}</td>
                        <td class="text-center">{{$action_plan->time_table}}</td>
                        <td class="text-center">{{$action_plan->person_responsible}}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="text-center" colspan="3">NO DATA</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="table table-sm">
            <thead>
                <tr class="bg-dark">
                    <th class="text-center">ACTIVITY</th>
                    <th class="text-center">NO OF DAYS (WEEKLY)</th>
                    <th class="text-center">NO OF DAYS (MTD)</th>
                    <th class="text-center">AREA/REMARKS</th>
                    <th class="text-center">NO OF DAYS (YTD)</th>
                    <th class="text-center">% TO TOTAL WORKING DAYS</th>
                </tr>
            </thead>
            <tbody>
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
                        <tr>
                            <td>{{$activity->activity}}</td>
                            <td class="text-center">{{$activity->no_of_days_weekly}}</td>
                            <td class="text-center">{{$activity->no_of_days_mtd}}</td>
                            <td class="text-center">{{$activity->remarks}}</td>
                            <td class="text-center">{{$activity->no_of_days_ytd}}</td>
                            <td class="text-center">{{$activity->percent_to_total_working_days}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">NO DATA</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center border-0">TOTAL</th>
                    <th class="text-center bl-0 br-0">{{$total_weekly}}</th>
                    <th class="text-center bl-0 br-0">{{$total_mtd}}</th>
                    <td class="border-0"></td>
                    <th class="text-center bl-0 br-0">{{$total_ytd}}</th>
                    <td class="border-0"></td>
                </tr>
            </tfoot>
        </table>

        @php
            $approval = $weekly_activity_report->approvals()->orderBy('created_at', 'DESC')->where('status', 'approved')->first();
        @endphp
        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="bg-gray">SUBMISSION</th>
                    <th class="bg-gray">NAME & SIGNATURE OF NSM</th>
                </tr>
                <tr>
                    <th class="text-danger">Tuesday of the ff. week</th>
                    <td rowspan="2" class="align-bottom text-uppercase text-center">
                        @if(!empty($approval))
                        {{$approval->user->fullName()}}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>SUBMIT TO NSM</th>
                </tr>
            </thead>
        </table>
    @endforeach

    {{-- DEVIATIONS --}}
    @foreach($deviations as $key => $deviation)

        @php
            $original_schedules = $deviation->schedules()->where('type', 'original')->get();
            $new_schedules = $deviation->schedules()->where('type', 'new')->get();
        @endphp

        <div class="page-break"></div>

        <div class="row">
            <div class="col-2">
                <img src="{{public_path('/assets/images/logo.jpg')}}" alt="logo" class="logo-img">
            </div>
            <div class="col-8 text-center">
                <label class="title">DEVIATION FORM</label>
            </div>
            <span class="float-right text-uppercase badge badge-{{$deviation_status_arr[$deviation->status]}}">{{$deviation->status}}</span>
        </div>

        <div class="container">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <th colspan="3" class="text-left text-uppercase">NAME: {{$deviation->user->fullName()}}</th>

                        <th class="text-left">
                            COST CENTER:
                            {{$deviation->cost_center}}
                        </th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-left">
                            REASON FOR DEVIATION:
                        </th>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-left">
                            {{$deviation->reason_for_deviation}}
                        </td>
                    </tr>
                </tbody>
            </table>

            <u>ORIGINAL PLAN</u>

            <table class="table table-sm mt-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="w200">ORIGINAL SCHEDULE</th>
                        <th class="w200">ACCOUNT AND AREA</th>
                        <th>ACTIVITY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($original_schedules as $schedule)
                    <tr>
                        <td></td>
                        <td>
                            {{$schedule->date}}
                        </td>
                        <td>
                            [{{$schedule->branch->account->short_name}}] - {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                        </td>
                        <td class="text-left">
                            {{$schedule->activity}}
                        </td>
                    </tr>
                    @endforeach

                    @if(empty($original_schedules->count()))
                    <tr>
                        <td colspan="4" class="text-center">NO DATA</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <u>NEW PLAN</u>

            <table class="table table-sm mt-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="w200">SCHEDULE</th>
                        <th class="w200">ACCOUNT AND AREA</th>
                        <th>ACTIVITY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($new_schedules as $schedule)
                    <tr>
                        <td></td>
                        <td>
                            {{$schedule->date}}
                        </td>
                        <td>
                            [{{$schedule->branch->account->short_name}}] - {{$schedule->branch->branch_code}} {{$schedule->branch->branch_name}}
                        </td>
                        <td class="text-left">
                            {{$schedule->activity}}
                        </td>
                    </tr>
                    @endforeach

                    @if(empty($new_schedules->count()))
                    <tr>
                        <td colspan="4" class="text-center">NO DATA</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <table class="table mt-2">
                <thead>
                    <tr>
                        <th class="text-left">
                            DATE FILED: 
                        </th>
                        <td>
                            {{date('Y-m-d', strtotime($deviation->created_at))}}
                        </td>

                        @php
                            $approval = $deviation->approvals()->orderBy('created_at', 'DESC')->where('status', 'approved')->first();
                        @endphp
                        <th class="text-left">DATE APPROVED: </th>
                        <td>
                            @if(!empty($approval)) {{date('Y-m-d', strtotime($approval->created_at))}} @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-left">
                            DATE OF DEVIATION: 
                        </th>
                        <td>{{$deviation->date}}</td>
                        
                        <th class="text-left">APPROVED BY:</th>
                        <td class="text-uppercase">
                            @if(!empty($approval)) {{$approval->user->fullName()}} @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0" colspan="3"></td>
                        <td class="text-center border-0">(Name & Signature)</td>
                    </tr>
                </thead>
            </table>

        </div>
        
    @endforeach

</body>
</html>