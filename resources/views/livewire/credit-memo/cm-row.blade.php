<div>
    <table class="table table-sm table-bordered text-xs">
        <tr>
            <td class="p-0 align-middle text-center">
                <button class="btn btn-xs btn-info" wire:click.pevent="showDetails()" title="view details">
                    @if($showDetail == 0)
                        <i class="fa fa-arrow-right"></i>
                    @else
                        <i class="fa fa-arrow-down"></i>
                    @endif
                </button>
            </td>
            <td class="p-0 align-middle text-center">{{$row_data['SalesOrderLine']}}</td>
            <td class="p-0 align-middle text-center">{{$row_data['StockCode']}}</td>
            <td class="py-0 align-middle text-left">{{$row_data['StockDescription']}}</td>
            <td class="p-0 align-middle">
                <input type="number" class="form-control form-control-sm border-0" placeholder="Quantity" wire:model.live="cm_row_details.quantity">
            </td>
            <td class="p-0 align-middle">
                <input type="text" class="form-control form-control-sm border-0" placeholder="Uom" wire:model.live="cm_row_details.uom">
            </td>
        </tr>
        @if($showDetail == 1)
            <tr>
                <td colspan="7">
                    <div class="row">
                        <div class="col-lg-6">
                            <table class="table table-sm table-bordered table-hover">
                                <tr>
                                    <th>Warehouse:</th>
                                    <td>{{$row_data['Warehouse']}}</td>
                                    <th>Unit Cost:</th>
                                    <td>{{$row_data['UnitCost']}}</td>
                                </tr>
                                <tr>
                                    <th>Bin:</th>
                                    <td>{{$row_data['Bin']}}</td>
                                    <th>Order UOM:</th>
                                    <td>{{$row_data['OrderUom']}}</td>
                                </tr>
                                <tr>
                                    <th>Order Quantity:</th>
                                    <td>{{$row_data['OrderQty']}}</td>
                                    <th>Stock Quantity to Ship:</th>
                                    <td>{{$row_data['StockQtyToShip']}}</td>
                                </tr>
                                <tr>
                                    <th>Ship Quantity:</th>
                                    <td>{{$row_data['ShipQty']}}</td>
                                    <th>Stocking UOM:</th>
                                    <td>{{$row_data['StockingUom']}}</td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td>{{$row_data['Price']}}</td>
                                    <th>Price Uom:</th>
                                    <td>{{$row_data['PriceUom']}}</td>
                                </tr>
                                <tr>
                                    <th>Line Ship Date:</th>
                                    <td colspan="3">{{$row_data['LineShipDate']}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-lg-6">
                            <table class="table table-sm table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Lot Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($row_data['lot_data'] as $lot_data)
                                        <tr>
                                            <td>{{$lot_data['Lot']}}</td>
                                            <td>{{$lot_data['StockQtyToShip']}}</td>
                                            <td>{{$lot_data['LotExpiryDate']}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        @endif
    </table>
</div>
