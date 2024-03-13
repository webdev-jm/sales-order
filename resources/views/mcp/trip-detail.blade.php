<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TRIP DETAILS</title>

    <style>
        .container {
            width: 100%;
            border: 2px solid black;
            padding-right: 7.5px;
            padding-left: 7.5px;
            margin-right: auto;
            margin-left: auto;
        }

        .logo-container {
            width: 100%;
            padding-top: 5px;
            padding-left: 10px;
        }
        .logo {
            height: 30px;
            vertical-align: middle;
            margin-top: 5px;
        }
        .bevi-logo {
            height: 30px;
            margin-right: 10px;
            vertical-align: middle;
        }

        .title-bar {
            border: 2px solid black;
            width: 99%;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
            background-color: rgb(219, 218, 218);
        }
        .title-bar-header {
            font-weight: 800;
            font-size: 25px;
            margin: 0 0 0 0;
        }

        .trip-number-container {
            padding-left: 10px;
            width: 27%;
        }
        .text-muted {
            font-weight: 500;
            color: rgb(83, 82, 82);
        }
        .text-center {
            text-align: center !important;
        }

        /* borders */
        .border-0 {
            border: 0 !important;
        }
        /* table */
        .table {
            width: 100%;
            margin-bottom: 1rem;
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
        .table.no-border tbody td,.table.no-border tbody th {
            border: 0 !important;
        }
        .table tr {
            display: table-row;
        }
        .table th, td {
            border: .5px solid;
            padding: 4px;
            font-size: 14px;
            text-align: left;
        }
        .table-sm td, th {
            padding: 0.3rem;
        }

        .border-left {
            border-left: 1px solid gray !important;
        }
        .align-top {
            vertical-align: top;
        }
        .align-bottom {
            vertical-align: bottom !important;
        }
        .text-right {
            text-align: right;
        }
        .objective {
            margin-top: 5px;
            padding-left: 10px;
        }

        .w33 {
            max-width: 33.33%;
        }
        .signatory-line {
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 1px solid black !important;
            height: 20px;
            text-align: center;
            font-weight: 800;
            font-size: 13px;
        }

        .footer {
            height: 15px;
            width: 100%;
            background-color: rgb(219, 218, 218);
            margin-left: -7.5px;
            border-top: 1px solid black;
            vertical-align: middle;
            padding-left: 5px;
            padding-right: 10px;
            padding-bottom: 5px;
        }
        .status-badge {
            padding-top: 5px;
            padding-bottom: 5px;
            padding-right: 6px;
            padding-left: 6px;
            float: right;
            margin-right: 12px;
            font-weight: 600;
            border-radius: 5%;
        }
        .badge-success {
            background-color: green;
            color: white;
        }
        .badge-danger {
            background-color: rgb(185, 0, 0);
            color: white;
        }
        .badge-secondary {
            background-color: rgb(99, 99, 99);
            color: white;
        }

        .bg-secondary {
            background-color: #6c757d!important;
            color: white;
        }
        .bg-warning {
            background-color: #ffc107!important;
        }
        .bg-primary {
            background-color: #007bff!important;
            color: white;
        }
        .bg-orange {
            background-color: #fd7e14!important;
        }
        .bg-info {
            background-color: #17a2b8!important;
            color: white;
        }
        .bg-success {
            background-color: #28a745!important;
            color: white;
        }
        .bg-danger {
            background-color: #dc3545!important;
            color: white;
        }
        .bg-indigo {
            background-color: indigo!important;
            color: white;
        }

    </style>
</head>
<body style="margin-left: 0;">
    
    <div class="container">
        {{-- header --}}
        <div class="logo-container">
            <img src="{{public_path('/assets/images/BEVI.jpg')}}" alt="logo" class="bevi-logo align-middle">
            <img src="{{public_path('/assets/images/asia.jpg')}}" alt="logo" class="logo align-middle">
            @if(!empty($trip->status))
                <span class="status-badge bg-{{$status_arr[$trip->status]}}">{{strtoupper($trip->status)}}</span>
            @endif
        </div>
        <div class="title-bar">
            <p class="title-bar-header">TRIP DETAILS</p>
        </div>

        <table class="table">
            <tbody>
                <tr>
                    <td class="border-0" rowspan="2">
                        {!! $bar_code !!}
                    </td>
                </tr>
                <tr>
                    <td class="border-0 align-middle text-center">
                        <span class="text-muted">TRIP CODE</span>
                        <br>
                        <strong>
                            {{$trip->trip_number}}
                        </strong>
                    </td>
                    <td class="border-0 align-middle text-center">
                        <span class="text-muted">TRANSPORTATION TYPE</span>
                        <br>
                        <strong>{{strtoupper($trip->transportation_type)}}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        <table class="table">
            <tbody>
                <tr>
                    <td class="border-0 w33">
                        <span class="text-muted">NAME</span>
                        <br>
                        <strong>{{strtoupper($trip->user->fullName())}}</strong>
                    </td>
                    @if(!empty($trip->activity_plan_detail))
                    <td class="border-0 border-left w33">
                        <span class="text-muted">BRANCH</span>
                        <br>
                        <strong>{{$trip->activity_plan_detail->branch->branch_code}} - {{$trip->activity_plan_detail->branch->branch_name}}</strong>
                    </td>
                    @endif
                    <td class="border-0 border-left w33">
                        <span class="text-muted">TRIP TYPE</span>
                        <br>
                        <strong>{{strtoupper(str_replace('_', ' ', $trip->trip_type))}}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="border-0">

                    </td>
                </tr>
                <tr>
                    <td class="border-0">
                        <span class="text-muted">DEPARTURE</span>
                        <br>
                        <strong>{{date('m/d/Y', strtotime($trip->departure))}}</strong>
                    </td>
                    @if($trip->type == 'round_trip')
                        <td class="border-0 border-left">
                            <span class="text-muted">RETURN</span>
                            <br>
                            <strong>{{date('m/d/Y', strtotime($trip->return))}}</strong>
                        </td>
                    @endif
                    <td class="border-0  border-left">
                        <span class="text-muted">PASSENGER/S</span>
                        <br>
                        <strong>{{$trip->passenger}}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="border-0">
                        
                    </td>
                </tr>
                <tr>
                    <td class="border-0">
                        <span class="text-muted">FROM</span>
                        <br>
                        <strong>{{strtoupper($trip->from)}}</strong>
                    </td>
                    <td class="border-0 border-left">
                        <span class="text-muted">TO</span>
                        <br>
                        <strong>{{$trip->to}}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>
        @if(!empty($trip->activity_plan_detail) || !empty($trip->purpose))
            <strong class="text-muted">OBJECTIVE</strong>
            <p class="objective">
                {{$trip->activity_plan_detail->activity ?? $trip->purpose}}
            </p>

            <hr>
        @endif

        {{-- FLIGHT DETAILS --}}

        <strong class="text-muted">AMOUNT</strong>
        <p class="objective">
            {{!empty($trip->amount) ? number_format($trip->amount, 2) : '-'}}
        </p>

        <hr>

        @php
            $approval = $trip->approvals()->where('status', 'for approval')->orderBy('created_at', 'ASC')->first();
        @endphp
        @if(!empty($approval))
            <strong class="text-muted">REMARKS</strong>
            <pre class="objective" style="font-family: 'Courier New', monospace;">{{$approval->remarks ?? '-'}}</pre>

            <hr>
        @endif

        <table class="table">
            <tbody>
                <tr>
                    @php
                        $approval  = $trip->approvals()->where('status', 'submitted')->orderBy('created_at', 'DESC')->first();
                    @endphp
                    @if(!empty($approval))
                        <td class="border-0 text-center">
                            
                            <span class="text-muted">SUBMITTED BY</span>
                            <br>
                            <input type="text" class="signatory-line" value="{{strtoupper($approval->user->fullName())}}">
                            <br>
                            <small>{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                        </td>
                    @endif
                    @php
                        $approval  = $trip->approvals()->where('status', 'approved by imm. superior')->orderBy('created_at', 'DESC')->first();
                    @endphp
                    @if(!empty($approval))
                        <td class="border-0 text-center">
                            <span class="text-muted">SUPERVISOR</span>
                            <br>
                            <input type="text" class="signatory-line" value="{{strtoupper($approval->user->fullName() ?? '')}}">
                            <br>
                            <small>{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                        </td>
                    @endif
                    @php
                        $approval = $trip->approvals()->where('status', 'for approval')->orderBy('created_at', 'DESC')->first();
                    @endphp
                    @if(!empty($approval))
                        <td class="border-0 text-center">
                            <span class="text-muted">ADMIN</span>
                            <br>
                            <input type="text" class="signatory-line align-bottom" value="{{strtoupper($approval->user->fullName())}}">
                            <br>
                            <small>{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                        </td>
                    @endif
                    @php
                        $approval = $trip->approvals()->where('status', 'approved by finance')->orderBy('created_at', 'DESC')->first();
                    @endphp
                    @if(!empty($approval))
                        <td class="border-0 text-center">
                            <span class="text-muted">FINANCE</span>
                            <br>
                            <input type="text" class="signatory-line align-bottom" value="{{strtoupper($approval->user->fullName())}}">
                            <br>
                            <small>{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}</small>
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Rev.00
        </div>

    </div>

</body>
</html>