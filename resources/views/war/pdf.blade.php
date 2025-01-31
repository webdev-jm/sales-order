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
    </style>
</head>
<body>
    
    <table class="table table-sm">
        <thead>
            <tr>
                {{-- logo --}}
                <th class="text-center" rowspan="2">
                    <img src="{{public_path('/assets/images/bevi-logo.png')}}" alt="logo" class="logo">
                </th>
                {{-- title --}}
                <th class="text-center align-middle title" rowspan="2">
                    WEEKLY PRODUCTIVITY REPORT
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
                <th class="text-center align-middle">BRANCHES</th>
                <th class="text-center align-middle">ACTION POINTS</th>
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

</body>
</html>