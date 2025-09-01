<div>
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">PAF DETAILS</h4>
        </div>
        <div class="modal-body">

            <div class="card">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Stock Code:</strong> {{$product_order['stock_code'] ?? ''}}
                        </li>
                        <li class="list-group-item">
                            <strong>Description:</strong> {{$product_order['description'] ?? ''}}
                        </li>
                        <li class="list-group-item">
                            <strong>Size:</strong> {{$product_order['size'] ?? ''}}
                        </li>
                        <li class="list-group-item">
                            <strong>Quantity:</strong>
                            @foreach($product_order['data'] ?? [] as $uom => $val)
                                <span class="badge badge-secondary">{{$uom}}: {{number_format($val['quantity'] ?? 0)}}</span>
                            @endforeach
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <thead>
                                <tr class="text-center">
                                    <th>PAF NUMBER</th>
                                    <th>UOM</th>
                                    <th>QUANTITY</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paf_rows as $index => $row)
                                    <tr>    
                                        <td class="p-0 align-middle">
                                            <select class="form-control border-0" wire:model="paf_rows.{{$index}}.paf_number">
                                                <option value="">- select -</option>
                                                @if(!empty($paf_details))
                                                    @foreach($paf_details as $paf)
                                                        <option value="{{$paf->PAFNo}}">{{$paf->PAFNo}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td class="p-0 align-middle text-center">
                                            <select class="form-control border-0" wire:model="paf_rows.{{$index}}.uom">
                                                <option value="">- select -</option>
                                                @foreach($uom_arr as $uom)
                                                    <option value="{{$uom}}">{{$uom}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-0 align-middle">
                                            <input type="number" class="form-control border-0" wire:model="paf_rows.{{$index}}.quantity" min="0" max="99999999999" placeholder="Enter quantity">
                                        </td>
                                        <td class="p-0 align-middle text-center">
                                            <button type="button" class="btn btn-danger btn-sm" wire:click.prevent="removeRow({{$index}})">
                                                <i class="fa fa-trash"></i>
                                            </button>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </thead>
                    </table>

                    <div class="text-right">
                        <button type="button" class="btn btn-info btn-sm" wire:click.prevent="addRow">ADD ROW</button>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer text-right">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-primary" wire:click.prevent="savePAF">SAVE</button>
        </div>
    </div>
</div>
