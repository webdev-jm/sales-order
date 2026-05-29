@extends('adminlte::page')

@section('title')
    RUD - Details
@endsection

@section('css')
@endsection

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>
                RUD / Details
            </h1>

        </div>
        <div class="col-md-6 text-right">
            <a href="{{route('cm.index')}}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>{{__('Back')}}</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        @if($credit_memo->status != 'draft')
            <div class="col-lg-12">
                <livewire:credit-memo.approvals :credit_memo="$credit_memo"/>
            </div>
        @endif

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">RUD Header</h3>
                </div>
                <div class="card-body p-1">
                    <ul class="list-group">
                        <li class="list-group-item py-1">
                            <strong>ACCOUNT:</strong>
                            <span class="float-right">
                                [{{ $credit_memo->account->account_code }}] - {{ $credit_memo->account->short_name }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>USER:</strong>
                            <span class="float-right">
                                {{ $credit_memo->user->fullName() }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>REASON:</strong>
                            <span class="float-right">
                                [{{ $credit_memo->reason->reason_code }}] - {{ $credit_memo->reason->reason_description }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>INVOICE NUMBER:</strong>
                            <span class="float-right">
                                {{ $credit_memo->invoice_number }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>PO NUMBER:</strong>
                            <span class="float-right">
                                {{ $credit_memo->po_number }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SO NUMBER:</strong>
                            <span class="float-right">
                                {{ $credit_memo->so_number }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>YEAR:</strong>
                            <span class="float-right">
                                {{ $credit_memo->year }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>MONTH:</strong>
                            <span class="float-right">
                                {{ $credit_memo->month }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Shipping Details</h3>
                </div>
                <div class="card-body p-1">
                    <ul class="list-group">
                        <li class="list-group-item py-1">
                            <strong>SHIP DATE:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_date ?? '-' }}
                            </span>
                        </li>
                        {{-- <li class="list-group-item py-1">
                            <strong>SHIP CODE:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_code }}
                            </span>
                        </li> --}}
                        <li class="list-group-item py-1">
                            <strong>SHIP NAME:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_name ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SHIPPING INSTRUCTION:</strong>
                            <span class="float-right">
                                {{ $credit_memo->shipping_instruction ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SHIP ADDRESS 1:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_address1 ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SHIP ADDRESS 2:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_address2 ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SHIP ADDRESS 3:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_address3 ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SHIP ADDRESS 4:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_address4 ?? '-' }}
                            </span>
                        </li>
                        <li class="list-group-item py-1">
                            <strong>SHIP ADDRESS 5:</strong>
                            <span class="float-right">
                                {{ $credit_memo->ship_address5 ?? '-' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">RUD Details</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-bordered text-sm">
                        <thead>
                            <tr>
                                <th>STOCK CODE</th>
                                <th>DESCRIPTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($credit_memo->cm_details as $detail)
                                <tr>
                                    <td>{{ $detail->product->stock_code }}</td>
                                    <td>{{ $detail->product->description }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="row">
                                            <div class="col-lg-5">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="card-title">DETAILS</h3>
                                                    </div>
                                                    <div class="card-body table-responsive p-0">
                                                        <table class="table table-bordered table-sm">
                                                            <tr>
                                                                <th>Warehouse:</th>
                                                                <td>{{$detail->warehouse}}</td>
                                                                <th>Unit Cost:</th>
                                                                <td>{{$detail->unit_cost}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Bin:</th>
                                                                <td></td>
                                                                <th>Order UOM:</th>
                                                                <td>{{$detail->order_uom}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Order Quantity:</th>
                                                                <td>{{$detail->order_quantity}}</td>
                                                                <th>Stock Quantity to Ship:</th>
                                                                <td>{{$detail->stock_quantity_to_ship}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Ship Quantity:</th>
                                                                <td>{{$detail->ship_quantity}}</td>
                                                                <th>Stocking UOM:</th>
                                                                <td>{{$detail->stocking_uom}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Price:</th>
                                                                <td>{{$detail->price}}</td>
                                                                <th>Price Uom:</th>
                                                                <td>{{$detail->price_uom}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Line Ship Date:</th>
                                                                <td colspan="3">{{$detail->line_ship_date}}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="card-title">LOT DETAILS</h3>
                                                    </div>
                                                    <div class="card-body p-0 table-responsive">
                                                        <table class="table table-bordered table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Lot</th>
                                                                    <th>Bin</th>
                                                                    <th>Uom</th>
                                                                    <th>Quantity</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $groups = $detail->cm_bins->groupBy(function($b){
                                                                        return ($b->lot_number ?? '') . '||' . ($b->bin ?? '');
                                                                    });
                                                                @endphp

                                                                @forelse($groups as $key => $items)
                                                                    @php
                                                                        [$lot, $bin] = explode('||', $key);
                                                                        $count = count($items);
                                                                    @endphp
                                                                    @foreach($items as $idx => $row)
                                                                        <tr>
                                                                            @if($idx == 0)
                                                                                <td rowspan="{{ $count }}">{{ $lot !== '' ? $lot : '-' }}</td>
                                                                                <td rowspan="{{ $count }}">{{ $bin !== '' ? $bin : '-' }}</td>
                                                                            @endif
                                                                            <td class="p-1">{{ $row->uom }}</td>
                                                                            <td class="text-right">{{ number_format($row->quantity) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="4" class="text-center">No lot/bin data</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            // Get the chat box element by its ID
            const chatBox = document.getElementById('chat-box');

            // Set the scroll position to the bottom
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        })
    </script>
@endsection

@section('footer')
@endsection

@section('right-sidebar')
sidebar
@endsection
