<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Proposal for Pick-Up (PPU) Form</title>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;

        color: #000;
    }

    .header {
        text-align: center;
        border-bottom: 3px solid #000;

        margin-bottom: 20px;
        background-color: #000;
    }

    .header img {
        height: 45px;
        float: left;
    }

    .header h1 {
        font-size: 24px;
        text-transform: uppercase;
        margin: 0;
        line-height: 45px;
        color: white;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .info-table td {
        padding: 5px 8px;
        vertical-align: top;
        font-size: 12px;
    }

    .info-table .label {
        font-weight: bold;
        text-transform: uppercase;
        width: 25%;
        white-space: nowrap;
    }

    table.data {
        width: 100%;
        border-collapse: collapse;
    }

    table.data th, table.data td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
        font-size: 11px;
    }

    table.data th {
        background-color: #f1f1f1;
        text-transform: uppercase;
        font-weight: bold;
    }

    .total-row td {
        font-weight: bold;
        background-color: #f9f9f9;
    }

    .footer-buttons {
        text-align: right;
        margin-top: 20px;
    }

    .btn {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 11px;
        text-transform: uppercase;
        margin-left: 6px;
    }

    .btn-draft {
        border: 1px solid #999;
        background-color: #ddd;
    }

    .btn-review {
        border: none;
        background-color: #007bff;
        color: #fff;
    }

</style>
</head>
<body>

    <div class="header">
        
         @if($ppu_form->account_login->account->company_id == 1)
        <img src="{{public_path('/images/bevi-logo-white (1).png')}}" alt="BEVI Logo">
        @elseif($ppu_form->account_login->account->company_id == 2)
        <img src="{{public_path('/images/bevalogo.jpg')}}" alt="logo"> 
        @else
        @endif
        <h1>Proposal for Pick-Up (PPU) Form</h1>
    </div>

    <!-- Info Section -->
    <table class="info-table">
        <tr>
            <td class="label">Customer Name:</td>
            <td>[{{$ppu_form->account_login->account->account_code}}] {{$ppu_form->account_login->account->short_name}}</td>
            <td class="label">PPU No:</td>
            <td>{{ $ppu_form->control_number }}</td>
        </tr>
        <tr>
            <td class="label">Prepared By:</td>
            <td>{{$ppu_form->account_login->user->fullName()}}</td>
            <td class="label">Date Submitted (SCM):</td>
            <td>{{$ppu_form->date_submitted}}</td>
        </tr>
        <tr>
            <td class="label">Date Prepared:</td>
            <td> {{$ppu_form->date_prepared}}</td>
            <td class="label">Propose Pick-Up Date:</td>
            <td> {{$ppu_form->pickup_date}}</td>
        </tr>
    </table>

    <!-- Table Section -->
    <table class="data">
        <thead>
            <tr>
                <th>No.</th>
                <th>RTV/RS No.</th>
                <th style="width: 60px;">RTV Date</th>
                <th>Branch Name</th>
                <th>Total Qty</th>
                <th>Total Amount</th>
                <th style="width: 50px;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ppuform_item as $index => $order)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $order['rtv_number'] ?? '' }}</td>
                <td>{{ $order['rtv_date'] ?? '' }}</td>
                <td>{{ $order['branch_name'] ?? '' }}</td>
                <td>{{ number_format($order['total_quantity'] ?? 0, 0) }}</td>
                <td>{{ number_format($order['total_amount'] ?? 0, 2) }}</td>
                <td>{{ $order['remarks'] ?? '' }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="4">TOTAL</td>
                <td>{{ $ppu_form['total_quantity'] }}</td>
                <td>{{ number_format($ppu_form['total_amount'], 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <p></p>

    <table class="info-table">
        <tr>
            <td class="label">APPROVALS:</td>
            <td></td>
            <td class="label">WITNESSED BY (During Pull-out):</td>
            <td></td>
        </tr>
        <tr>
            <td>
            <table class="data">
                <tr>
                    <td class="label"></td>
                    <td >Name:</td>
                    <td>Date:</td>

                </tr>
                <tr>
                    <td class="label">GSM:</td>
                    <td></td>
                    <td></td>

                </tr>
                <tr>
                    <td class="label">NSM</td>
                    <td></td>
                    <td></td>
        

                </tr>
                <tr>
                    <td class="label">SD:</td>
                    <td></td>
                    <td></td>

                </tr>
                <tr>
                    <td class="label">FINANCE:</td>
                    <td style=" width: 150px;"></td>
                    <td style=" width: 50px;"></td>
                </tr>
            </table>
            </td>
            <td></td>
            <td> 
            <table class="data">
                <tr>
                    <td class="label"></td>
                    <td>Name:</td>
                    <td>Date:</td>
                </tr>
                <tr>
                    <td class="label">BEVA Representative:</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="label">Customer/BO Custodian:</td>
                    <td></td>
                    <td></td>

                </tr>
                <tr>
                    <td class="label">Trucker:</td>
                    <td style=" width: 140px;"></td>
                    <td style=" width: 40px;"></td>
                </tr>
                <tr>
                    <td class="label">Plate NO.:</td>
                    <td colspan="2"></td>
                </tr>
            </table>
            </td>
        </tr>

          

    </table>

</body>
</html>
