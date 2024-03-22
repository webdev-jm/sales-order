<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">SALES ORDER SUMMARY</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-12">
                    <h4>{{$data['control_number'] ?? '-'}}</h4>
                </div>
            </div>

            <hr class="my-0">

            <div class="row">
                <div class="col-lg-6">
                    <strong class="text-muted">SHIP TO ADDRESS</strong>
                    <br>
                    <b class="text-uppercase">{{$ship_to_address['ship_to_name'] ?? '-'}}</b>
                    <br>
                    <span>{{$ship_to_address['ship_to_address1'] ?? '-'}}</span>
                    <br>
                    <span>{{$ship_to_address['ship_to_address2'] ?? '-'}}</span>
                    <br>
                    <span>{{$ship_to_address['ship_to_address3'] ?? '-'}}</span>
                </div>

                <div class="col-lg-6">
                    <b>PO NUMBER: {{$data['po_number'] ?? '-'}}</b>
                    <br>
                    <b>PO VALUE:</b> <span>{{$order_data['po_value'] ?? '-'}}</span>
                    <br>
                    <b>ORDER DATE:</b> <span>{{$data['order_date'] ?? '-'}}</span>
                    <br>
                    <b>SHIP DATE:</b> <span>{{$data['ship_date'] ?? '-'}}</span>
                    <br>
                    <b>DISCOUNT:</b> <span>{{$account->discount->description ?? '-'}}</span>
                    <br>
                    <b>ACCOUNT:</b> <span>{{'['.$account->account_code.'] '.$account->short_name}}</span>
                </div>

                <div class="col-lg-12">
                    <strong class="text-muted">SHIPPING INSTRUCTION</strong>
                    <p>{{$data['shipping_instruction'] ?? '-'}}</p>
                </div>
                
            </div>

            <hr class="my-1">

            <div class="col-12 table-responsive" style="max-height: 450px">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr class="text-center">
                            <th class="bg-gray" style="position: sticky; top:-1px">#</th>
                            <th class="align-middle p-0 bg-gray" style="position: sticky; top:-1px">STOCK CODE</th>
                            <th class="align-middle p-0 bg-gray" style="position: sticky; top:-1px">DESCRIPTION</th>
                            <th class="align-middle p-0 bg-gray" style="position: sticky; top:-1px">UNIT</th>
                            <th class="align-middle p-0 bg-gray" style="position: sticky; top:-1px">QUANTITY</th>
                            <th class="align-middle p-0 bg-gray" style="position: sticky; top:-1px">TOTAL</th>
                            <th class="align-middle p-0 bg-gray" style="position: sticky; top:-1px">TOTAL LESS DISCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($order_data['items']))
                            @php
                                $num = 0;
                            @endphp
                            @foreach($order_data['items'] as $item)
                                @php
                                    $num++;
                                @endphp
                                <tr>
                                    <td class="align-middle">{{$num}}</td>
                                    <td class="align-middle">{{$item['stock_code']}}</td>
                                    <td class="align-middle">{{$item['description'].' '.$item['size']}}</td>
                                    @foreach($item['data'] as $unit => $val)
                                        <td class="text-center align-middle">
                                            {{$unit}}
                                        </td>
                                        <td class="text-right align-middle">
                                            {{number_format($val['quantity'] ?? 0)}}
                                        </td>
                                        <td class="text-right align-middle">
                                            {{number_format($val['total'] ?? 0, 2)}}
                                        </td>
                                        <td class="text-right align-middle">
                                            {{number_format($val['discounted'] ?? 0, 2)}}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="bg-white" style="position: sticky; bottom:-1px">TOTAL</th>
                            <th class="text-right bg-white" style="position: sticky; bottom:-1px">{{number_format($order_data['total_quantity'] ?? 0)}}</th>
                            <th class="text-right bg-white" style="position: sticky; bottom:-1px">{{number_format($order_data['total'] ?? 0, 2)}}</th>
                            <th class="text-right bg-white" style="position: sticky; bottom:-1px">{{number_format($order_data['grand_total'] ?? 0, 2)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr class="my-1">

            <div class="col-lg-8">
                <strong class="text-muted">ORDER SUMMARY</strong>
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th>TOTAL:</th>
                            <td>{{number_format($order_data['total'] ?? 0, 2)}}</td>
                        </tr>
                        <tr>
                            <th>DISCOUNT</th>
                            <td>{{$account->discount->description ?? '-'}}</td>
                        </tr>
                        <tr>
                            <th>TOTAL LESS DISCOUNT</th>
                            <td>{{number_format($order_data['grand_total'] ?? 0, 2)}}</td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" id="btn-finalize">SUBMIT</button>
        </div>
    </div>
</div>
