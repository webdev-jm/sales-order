<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Http\Traits\SoProductPriceTrait;
use App\Models\Product;

class CmRow extends Component
{
    use SoProductPriceTrait;

    public $row_data;
    public $showDetail;
    public $cm_details;
    public $cm_row_details;
    public $product;

    public function render()
    {
        return view('livewire.credit-memo.cm-row');
    }

    public function mount() {
        $this->cm_details = Session::get('cm_details');
        if($this->cm_details && isset($this->cm_details[$this->row_data['StockCode']])) {
            $this->cm_row_details = $this->cm_details[$this->row_data['StockCode']];
            $this->showDetail = 1;
        } else {
            $this->cm_row_details = [];
            $this->showDetail = 0;

        }
        $this->product = Product::where('stock_code', $this->row_data['StockCode'])->first();
        foreach($this->row_data['bin_data'] as $key => $row_data) {
            $cs_conversion = $this->uomConversion($row_data['StockQtyToShip'],$this->row_data['StockCode'], $this->row_data['StockingUom'], $this->row_data['OrderUom']);
            $this->row_data['bin_data'][$key]['conversion'] = $cs_conversion;
        }
    }

    public function showDetails() {
        if($this->showDetail) {
            $this->showDetail = 0;
        } else {
            $this->showDetail = 1;
        }

        $this->setSession();
    }

    public function setSession() {
        $cm_details = Session::get('cm_details');
        $this->cm_row_details['product'] = $this->product;
        $this->cm_row_details['row_data'] = [
            'warehouse' => $this->row_data['Warehouse'],
            'bin' => $this->row_data['Bin'],
            'order_quantity' => $this->row_data['OrderQty'],
            'ship_quantity' => $this->row_data['ShipQty'],
            'price' => $this->row_data['Price'],
            'line_ship_date' => $this->row_data['LineShipDate'],
            'unit_cost' => $this->row_data['UnitCost'],
            'order_uom' => $this->row_data['OrderUom'],
            'stock_quantity_to_ship' => $this->row_data['StockQtyToShip'],
            'stocking_uom' => $this->row_data['StockingUom'],
            'price_uom' => $this->row_data['PriceUom'],
        ];
        $cm_details[$this->row_data['StockCode']] = $this->cm_row_details;
        if($this->showDetail == 0) {
            unset($cm_details[$this->row_data['StockCode']]);
        } else {
            if(empty($this->cm_row_details['data'])) {
                $cm_details[$this->row_data['StockCode']]['data'] = $this->row_data['bin_data'];
            }
        }
        Session::put('cm_details', $cm_details);
    }

    public function selectBin($key) {
        if($this->showDetail && empty($this->cm_row_details[$key])) {
            $this->cm_row_details['data'][$key] = $this->row_data['bin_data'][$key];
        } else {
            unset($this->cm_row_details['data'][$key]);
        }

        $this->setSession();
    }
}
