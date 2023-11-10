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
    </style>
</head>
<body style="margin-left: 0;">
    
    <div class="container">
        {{-- header --}}
        <div class="logo-container">
            <img src="{{public_path('/assets/images/BEVI.jpg')}}" alt="logo" class="bevi-logo align-middle">
            <img src="{{public_path('/assets/images/asia.jpg')}}" alt="logo" class="logo align-middle">
        </div>
        <div class="title-bar">
            <p class="title-bar-header">TRIP DETAILS</p>
        </div>

        <table class="table">
            <tbody>
                <tr>
                    <td class="border-0">
                        {!! $bar_code !!}
                        <strong>
                            {{$trip->trip_number}}
                        </strong>
                    </td>
                    <td class="border-0 align-top text-right">
                        <span class="text-muted">TRANSPORTATION TYPE</span>
                        <br>
                        <strong>{{strtoupper($trip->transportation_type)}}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        @if($trip->source == 'activity-plan')
            <table class="table">
                <tbody>
                    <tr>
                        <td class="border-0 w33">
                            <span class="text-muted">NAME</span>
                            <br>
                            <strong>{{strtoupper($trip->activity_plan_detail->activity_plan->user->fullName())}}</strong>
                        </td>
                        <td class="border-0 border-left w33">
                            <span class="text-muted">BRANCH</span>
                            <br>
                            <strong>{{$trip->activity_plan_detail->branch->branch_code}} - {{$trip->activity_plan_detail->branch->branch_name}}</strong>
                        </td>
                        <td class="border-0 border-left w33">
                            <span class="text-muted">DATE</span>
                            <br>
                            <strong>{{date('m/d/Y', strtotime($trip->activity_plan_detail->date))}}</strong>
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
                            <strong>{{strtoupper($trip->departure)}}</strong>
                        </td>
                        <td class="border-0 border-left">
                            <span class="text-muted">ARRIVAL</span>
                            <br>
                            <strong>{{$trip->arrival}}</strong>
                        </td>
                        @if(!empty($trip->reference_number))
                            <td class="border-0 border-left">
                                <span class="text-muted">REFERENCE NUMBER</span>
                                <br>
                                <strong>{{$trip->reference_number}}</strong>
                            </td>
                        @else
                            <td class="border-0">

                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>

            <hr>

            <strong class="text-muted">OBJECTIVE</strong>
            <p class="objective">
                {{$trip->activity_plan_detail->activity}}
            </p>

            <hr>

            <table class="table">
                <tbody>
                    <tr>
                        @php
                            $approval  = $trip->activity_plan_detail->activity_plan->approvals()->where('status', 'submitted')->orderBy('created_at', 'DESC')->first();
                        @endphp
                        @if(!empty($approval))
                            <td class="border-0 text-center">
                                
                                <span class="text-muted">SUBMITTED BY</span>
                                <br>
                                <input type="text" class="signatory-line" value="{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}">
                                <br>
                                <strong>{{strtoupper($approval->user->fullName())}}</strong>
                            </td>
                        @endif
                        @php
                            $approval  = $trip->activity_plan_detail->activity_plan->approvals()->where('status', 'approved')->orderBy('created_at', 'DESC')->first();
                        @endphp
                        @if(!empty($approval))
                            <td class="border-0 text-center">
                                <span class="text-muted">APPROVED BY</span>
                                <br>
                                <input type="text" class="signatory-line" value="{{date('m/d/Y H:i:s a', strtotime($approval->created_at))}}">
                                <br>
                                <strong>{{strtoupper($approval->user->fullName() ?? '')}}</strong>
                            </td>
                        @endif
                        @php
                            $trip_approval = $trip->approvals()->where('status', 'approved')->orderBy('created_at', 'DESC')->first();
                        @endphp
                        @if(!empty($trip_approval))
                            <td class="border-0 text-center">
                                <span class="text-muted">APPROVED BY</span>
                                <br>
                                <input type="text" class="signatory-line align-bottom" value="{{date('m/d/Y H:i:s a', strtotime($trip_approval->created_at))}}">
                                <br>
                                <strong>{{strtoupper($trip_approval->user->fullName())}}</strong>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        @else
            <table class="table">
                <tbody>
                    <tr>
                        <td class="border-0 w33">
                            <span class="text-muted">NAME</span>
                            <br>
                            <strong>{{strtoupper($trip->schedule->user->fullName())}}</strong>
                        </td>
                        <td class="border-0 border-left w33">
                            <span class="text-muted">BRANCH</span>
                            <br>
                            <strong>{{$trip->schedule->branch->branch_code}} - {{$trip->schedule->branch->branch_name}}</strong>
                        </td>
                        <td class="border-0 border-left w33">
                            <span class="text-muted">DATE</span>
                            <br>
                            <strong>{{date('m/d/Y', strtotime($trip->schedule->date))}}</strong>
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
                            <strong>{{strtoupper($trip->departure)}}</strong>
                        </td>
                        <td class="border-0 border-left">
                            <span class="text-muted">ARRIVAL</span>
                            <br>
                            <strong>{{$trip->arrival}}</strong>
                        </td>
                        @if(!empty($trip->reference_number))
                            <td class="border-0 border-left">
                                <span class="text-muted">REFERENCE NUMBER</span>
                                <br>
                                <strong>{{$trip->reference_number}}</strong>
                            </td>
                        @else
                            <td class="border-0">

                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>

            <hr>

            <strong class="text-muted">OBJECTIVE</strong>
            <p class="objective">
                {{$trip->schedule->objective}}
            </p>

            <hr>

            <table class="table">
                <tbody>
                    <tr>
                        <td class="border-0 text-center">
                            
                            <span class="text-muted">SUBMITTED BY</span>
                            <br>
                            <input type="text" class="signatory-line" value="{{date('m/d/Y H:i:s a', strtotime($trip->created_at))}}">
                            <br>
                            <strong>{{strtoupper($trip->schedule->user->fullName())}}</strong>
                        </td>
                        <td class="border-0 text-center">
                            <span class="text-muted">APPROVED BY</span>
                            <br>
                            <input type="text" class="signatory-line" value="">
                            <br>
                            <strong></strong>
                        </td>
                        @php
                            $trip_approval = $trip->approvals()->where('status', 'approved')->orderBy('created_at', 'DESC')->first();
                        @endphp
                        @if(!empty($trip_approval))
                            <td class="border-0 text-center">
                                <span class="text-muted">APPROVED BY</span>
                                <br>
                                <input type="text" class="signatory-line align-bottom" value="{{date('m/d/Y H:i:s a', strtotime($trip_approval->created_at))}}">
                                <br>
                                <strong>{{strtoupper($trip_approval->user->fullName())}}</strong>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="footer">
            Rev.1
        </div>

    </div>

</body>
</html>