<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">SALES ORDER SUMMARY</h4>
        </div>
        <div class="modal-body">
            @if(!empty($so_data))

                <div class="table-resposive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Ship To Name</th>
                                <th>Address</th>
                                <th>Total Quantity</th>
                                <th>Total Anount</th>
                                <th>Net Amount</th>
                                <th>Net Amount</th>
                                <th>PO Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($so_data as $po_number => $data)
                                <tr>
                                    <td>{{$po_number}}</td>
                                    @if(!empty($data['shipping_address']))
                                        <td>
                                            [{{$data['shipping_address']['address_code']}}] {{$data['shipping_address']['ship_to_name']}}
                                        </td>
                                        <td>
                                            {{$data['shipping_address']['building']}}, {{$data['shipping_address']['street']}}, {{$data['shipping_address']['city']}}, {{$data['shipping_address']['postal']}}
                                        </td>
                                    @else
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
{{--
                {{dd($so_data)}} --}}

            @endif
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" id="btn-finalize">FINALIZE</button>
        </div>
    </div>
</div>
