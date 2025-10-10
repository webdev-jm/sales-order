<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">MULTIPLE SO SUMMARY</h4>
        </div>
        <div class="modal-body">
            @if(!empty($so_data))

            {{-- {{dd($so_data)}} --}}

                <div class="table-resposive">
                    <table class="table table-bordered table-hover table-sm text-xs">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle p-0">PO Number</th>
                                <th class="align-middle p-0">Ship To Name</th>
                                <th class="align-middle p-0">Address</th>
                                <th class="align-middle p-0">Ship Date</th>
                                <th class="align-middle p-0">Total Quantity</th>
                                <th class="align-middle p-0">Total Amount</th>
                                <th class="align-middle p-0">Net Amount</th>
                                <th class="align-middle p-0">PO Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po_data as $po_number => $data)
                                <tr>
                                    <td class="p-0 pl-1 text-left">{{$data['po_number']}}</td>
                                    <td class="p-0 pl-1 text-left">{{$data['ship_to_name']}}</td>
                                    <td class="p-0 pl-1 text-left">{{$data['address']}}</td>
                                    <td class="p-0 pl-1 text-left">{{$data['ship_date']}}</td>
                                    <td class="p-0 pr-1 text-right">{{number_format($data['total_quantity'])}}</td>
                                    <td class="p-0 pr-1 text-right">{{number_format($data['total_amount'], 2)}}</td>
                                    <td class="p-0 pr-1 text-right">{{number_format($data['net_amount'], 2)}}</td>
                                    <td class="p-0 pr-1 text-right">{{number_format($data['po_value'], 2)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-center">GRAND TOTAL</th>
                                <th class="text-right">{{number_format($grand_total['quantity'])}}</th>
                                <th class="text-right">{{number_format($grand_total['amount'], 2)}}</th>
                                <th class="text-right">{{number_format($grand_total['net_amount'], 2)}}</th>
                                <th class="text-right">{{number_format($grand_total['po_value'], 2)}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>


            @endif
        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" wire:click.prevent="finalizeAll" data-dismiss="modal">FINALIZE ALL</button>
        </div>
    </div>
</div>
