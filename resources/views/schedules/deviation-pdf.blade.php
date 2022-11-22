<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DEVIATION FORM</title>

    <style>
        /* typography */
        .text-center {
            text-align: center !important;
        }
        .text-left {
            text-align: left !important;
        }
        .text-uppercase {
            text-transform: uppercase!important;
        }
        .align-middle {
            vertical-align: middle!important;
        }

        .border-0 {
            border: 0 !important;
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
            margin-right: -7.5px;
            margin-left: -7.5px;
        }
        .col-2 {
            position: relative;
            display: inline;
            flex: 0 0 16%;
            width: 17%;
            padding-right: 7.5px;
            padding-left: 7.5px;
        }
        .col-10 {
            position: relative;
            display: inline;
            text-align: center;
            flex: 0 0 83%;
            width: 83%;
            padding-right: 7.5px;
            padding-left: 7.5px;
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

        /* table */
        .table {
            width: 100%;
            margin-bottom: 0.3rem;
            border-collapse: collapse;
        }
        .table thead {
            display: table-header-group;
            vertical-align: middle;
        }
        .table tbody {
            display: table-row-group;
            vertical-align: middle;
        }
        .table tr {
            display: table-row;
        }
        .table th, td {
            border: 1px solid rgb(148, 148, 148);
            padding: 3px;
            font-size: 11px;
            text-align: center;
        }
        .table-sm td, th {
            padding: 0.2rem;
        }
    </style>
</head>
<body>

    <div class="row">
        <div class="col-2">
            <img src="{{public_path('/assets/images/logo.jpg')}}" alt="logo" class="logo-img">
        </div>
        <div class="col-10 text-center">
            <label class="title">DEVIATION FORM</label>
        </div>
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
            </tbody>
        </table>

        <table class="table mt-2">
            <thead>
                <tr>
                    <th class="text-left">
                        DATE FILED: {{date('Y-m-d', strtotime($deviation->created_at))}}
                        <br>
                        DATE OF DEVIATION: {{$deviation->date}}
                    </th>
                    @php
                        $approval = $deviation->approvals()->orderBy('created_at', 'DESC')->where('status', 'approved')->first();
                    @endphp
                    <th class="text-left text-uppercase">
                        DATE APPROVED: @if(!empty($approval)) {{date('Y-m-d', strtotime($approval->created_at))}} @endif
                        <br>
                        APPROVED BY: @if(!empty($approval)) {{$approval->user->fullName()}} @endif
                    </th>
                </tr>
                <tr>
                    <td class="border-0"></td>
                    <td class="text-center border-0">(Name & Signature)</td>
                </tr>
            </thead>
        </table>

    </div>
</body>
</html>
