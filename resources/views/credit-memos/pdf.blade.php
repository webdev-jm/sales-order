<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Return Upon Delivery</title>


    <style>
        .text-center {
            text-align: center !important;
        }
        .text-left {
            text-align: left !important;
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
        .sub-title {
            font-size: 14px !important;
        }
        .val {
            font-size: 12px !important;
        }
        .logo-bevi {
            width: 250px;
        }
        .logo-beva {
            width: 80px;
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
        .mt-5 {
            margin-top: 50px !important;
        }

        .header-title {
            font-size: 12px !important;
            font-weight: bold !important;
            width: 200px !important;
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
    @php
        $submitted = $credit_memo->approvals()->where('status', 'submitted')
            ->orderBy('created_at', 'desc')
            ->first();

        $noted = $credit_memo->approvals()->where('status', 'for approval')
            ->orderBy('created_at', 'desc')
            ->first();

        $approved = $credit_memo->approvals()->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->first();

    @endphp

    <table class="table table-sm">
        <thead>
            <tr>
                {{-- logo --}}
                <th class="text-center align-middle">
                    @if($credit_memo->account->company->name == 'BEVA')
                        <img src="{{public_path('/assets/images/asia.jpg')}}" alt="logo" class="logo-beva">
                    @else
                        <img src="{{public_path('/assets/images/BEVI.jpg')}}" alt="logo" class="logo-bevi">
                    @endif
                </th>
                {{-- title --}}
                <th class="text-center align-middle title">
                    RETURN UPON DELIVERY
                </th>
            </tr>
        </thead>
    </table>

    <table class="table table-sm">
        <thead>
            <tr>
                <th class="align-middle border-0 header-title">CUSTOMER NAME:</th>
                <td class="align-middle border-0">{{ $credit_memo->account->account_code }} - {{ $credit_memo->account->short_name }}</td>

                <th class="align-middle border-0 header-title">SUBMITTED DATE: </th>
                <td class="align-middle border-0">
                    {{ $submitted->created_at ? date('Y-m-d', strtotime($submitted->created_at)) : '-'}}
                </td>
            </tr>
            <tr>
                <th class="align-middle border-0 header-title">INVOICE NUMBER:</th>
                <td class="align-middle border-0">{{ $credit_memo->invoice_number ?? '-'}}</td>

                <th class="align-middle border-0 header-title">CM REASON:</th>
                <td class="align-middle border-0">{{ $credit_memo->reason->reason_code ?? '-'}} - {{ $credit_memo->reason->reason_description ?? '-'}}</td>
            </tr>
            <tr>
                <th class="align-middle border-0 header-title">CUSTOMER PO NUMBER:</th>
                <td class="align-middle border-0">{{ $credit_memo->po_number ?? '-'}}</td>

                <th class="align-middle border-0"></th>
                <th class="align-middle border-0"></th>
            </tr>
            <tr>
                <th class="align-middle border-0 header-title">INVOICE DATE:</th>
                <td class="align-middle border-0">{{ $credit_memo->cm_date ?? '-'}}</td>

                <th class="align-middle border-0"></th>
                <td class="align-middle border-0"></td>
            </tr>
            <tr>
                <th class="align-middle border-0 header-title">SHIP ADDRESS:</th>
                <td class="align-middle border-0" colspan="3">
                    {{ $credit_memo->ship_name ?? ''}} {{ $credit_memo->ship_address1 ?? ''}} {{ $credit_memo->ship_address2 ?? ''}}
                    {{ $credit_memo->ship_address3 ?? ''}} {{ $credit_memo->ship_address4 ?? ''}} {{ $credit_memo->ship_address5 ?? ''}}
                </td>
            </tr>
        </thead>
    </table>

    <table class="table table-sm">
        <thead>
            <tr>
                <th colspan="6" class="bg-dark sub-title">DETAILS</th>
            </tr>
            <tr>
                <th class="align-middle bg-gray">STOCK CODE</th>
                <th class="align-middle bg-gray">DESCRIPTION</th>
                <th class="align-middle bg-gray">WAREHOUSE</th>
                <th class="align-middle bg-gray">LOT NUMBER</th>
                <th class="align-middle bg-gray">QUANTITY</th>
                <th class="align-middle bg-gray">UOM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($credit_memo->cm_details as $detail)
                @php
                    $bins = $detail->cm_bins()->where('uom', $detail->order_uom)->get();
                @endphp
                <tr>
                    <td>{{ $detail->product->stock_code }}</td>
                    <td>{{ $detail->product->description }}</td>
                    <td>{{ $detail->warehouse }}</td>
                    <td>
                        @foreach($bins as $bin)
                            {{ $bin->lot_number }} <br>
                        @endforeach
                    </td>
                    <td class="text-center">
                        @foreach($bins as $bin)
                            {{ number_format($bin->quantity, 2) }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($bins as $bin)
                            {{ $bin->uom }} <br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="table table-sm mt-5">
        <thead>
            <tr>
                <th class="text-left sub-title border-0">PREPARED BY: {{ $submitted->user->fullName() ?? '-' }}</th>
            </tr>
            <tr>
                <th class="text-left sub-title border-0">NOTED BY: {{ $noted->user->fullName() ?? '-' }}</th>
            </tr>
            <tr>
                <th class="text-left sub-title border-0">APPROVED BY: {{ $approved->user->fullName() ?? '-' }}</th>
            </tr>
        </thead>
    </table>

</body>
</html>
