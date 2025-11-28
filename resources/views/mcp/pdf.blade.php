<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Activity Plan</title>

    <style>
        .text-uppercase {
            text-transform: uppercase;
        }
        .text-center {
            text-align: center !important;
        }
        .text-left {
            text-align: left !important;
        }
        .text-primary {
            color: rgb(9, 12, 201);
            font-weight: 600;
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
            border: 1.2px solid rgb(16, 16, 16);
            padding: 4px;
            font-size: 11px;
            text-align: center;
        }
        .table-sm td, th {
            padding: 0.3rem;
        }
    </style>
</head>
<body>

    <div class="mb-3">
        <label>
            <span class="font-weight-bold">ACTIVITY PLAN FOR THE MONTH OF</span> <span class="text-uppercase text-primary">{{date('F Y', strtotime($activity_plan->year.'-'.$activity_plan->month.'-01'))}}</span>
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
    </div>

    <div class="mb-3">
        <label class="font-weight-bold">OBJECTIVE FOR THE MONTH</label>
        <pre class="objective mt-0">{{trim($activity_plan->objectives)}}</pre>
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
                <th class="text-center">TRIP #</th>
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
                            <span class="">[{{$line['account_name']}}]</span>
                        @endif
                    </td>
                    <td class="text-left">{{$line['purpose']}}</td>
                    <td class="mw-100">{{$line['work_with']}}</td>
                    <td class="mw-100">{{$line['trip']}}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>
