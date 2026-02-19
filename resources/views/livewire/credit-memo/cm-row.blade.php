<tbody class="table table-sm table-bordered text-xs">
    <tr>
        <td class="p-0 align-middle text-center" width="50">
            <button class="btn btn-xs btn-{{ $showDetail ? 'success' : 'secondary' }}"
                    wire:click.prevent="toggleDetails">
                <i class="fa {{ $showDetail ? 'fa-check-circle' : 'far fa-circle' }}"></i>
            </button>
        </td>
        <td class="align-middle text-center">{{ $row_data['SalesOrderLine'] }}</td>
        <td class="align-middle text-center"><strong>{{ $this->stockCode }}</strong></td>
        <td class="align-middle">{{ $row_data['StockDescription'] }}</td>
    </tr>

    @if($showDetail)
        <tr>
            <td colspan="4">
                <div class="row p-2 bg-light">
                    <div class="col-lg-5">
                        <table class="table table-sm table-bordered bg-white">
                             @foreach(['Warehouse', 'Bin', 'OrderQty', 'Price', 'StockingUom'] as $field)
                                <tr>
                                    <th class="bg-light w-25">{{ $field }}:</th>
                                    <td>{{ $row_data[$field] ?? '-' }}</td>
                                </tr>
                             @endforeach
                        </table>
                    </div>

                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>LOT DETAILS</strong>
                            <div>
                                <button class="btn btn-xs btn-outline-success" wire:click.prevent="selectAllBins">All</button>
                                <button class="btn btn-xs btn-outline-danger" wire:click.prevent="clearAllBins">Clear</button>
                            </div>
                        </div>
                        <table class="table table-sm table-bordered table-hover text-xs bg-white">
                            <thead><tr><th>Select</th><th>Lot</th><th>Bin</th><th>Qty</th></tr></thead>
                            <tbody>
                                @foreach($row_data['bin_data'] as $key => $bin)
                                    @php $isSelected = isset($cm_row_details['data'][$bin['composite_key']]); @endphp
                                    <tr class="{{ $isSelected ? 'table-success' : '' }}">
                                        <td class="text-center">
                                            <button class="btn btn-xs btn-{{ $isSelected ? 'success' : 'light border' }}"
                                                    wire:click.prevent="selectBin({{ $key }})">
                                                <i class="fa {{ $isSelected ? 'fa-check-circle' : 'far fa-circle' }}"></i>
                                            </button>
                                        </td>
                                        <td>{{ $bin['Lot'] }}</td>
                                        <td>{{ $bin['Bin'] }}</td>
                                        <td>{{ $bin['conversion'][$row_data['OrderUom']] ?? '0' }}</td>
                                        <td>{{ $row_data['OrderUom'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    @endif
</tbody>
