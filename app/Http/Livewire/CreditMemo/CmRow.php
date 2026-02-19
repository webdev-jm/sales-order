<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Http\Traits\SoProductPriceTrait;
use App\Models\Product;

class CmRow extends Component
{
    use SoProductPriceTrait;

    public array $row_data;
    public bool $showDetail = false;
    public array $cm_row_details = ['data' => []];
    public $product;

    public function mount()
    {
        $this->product = Product::where('stock_code', $this->stockCode)->first();
        $this->precomputeConversions();

        // Check if this row is already in session (selected)
        $all_details = Session::get('cm_details', []);
        if (isset($all_details[$this->stockCode])) {
            $this->cm_row_details = $all_details[$this->stockCode];
            $this->showDetail = true;
        }
    }

    public function getStockCodeProperty()
    {
        return $this->row_data['StockCode'];
    }

    protected function precomputeConversions()
    {
        foreach ($this->row_data['bin_data'] as $key => $bin) {
            $this->row_data['bin_data'][$key]['conversion'] = $this->uomConversion(
                $bin['StockQtyToShip'],
                $this->stockCode,
                $this->row_data['StockingUom'],
                $this->row_data['OrderUom']
            );
            $this->row_data['bin_data'][$key]['composite_key'] = "{$bin['Lot']}-{$bin['Bin']}";
        }
    }

    public function toggleDetails()
    {
        $this->showDetail = !$this->showDetail;
        if ($this->showDetail) {
            $this->initializeRowDetails();
        } else {
            $this->removeFromSession();
        }
    }

    protected function initializeRowDetails()
    {
        $this->cm_row_details['product'] = $this->product;
        // Only store essential data in session to reduce size
        $this->cm_row_details['row_data'] = collect($this->row_data)
            ->only(['Warehouse', 'Bin', 'OrderQty', 'ShipQty', 'Price', 'UnitCost', 'OrderUom', 'StockingUom'])
            ->toArray();

        if (empty($this->cm_row_details['data'])) {
            $this->selectAllBins();
        } else {
            $this->syncToSession();
        }
    }

    public function selectBin($key)
    {
        $bin = $this->row_data['bin_data'][$key];
        $cKey = $bin['composite_key'];

        if (isset($this->cm_row_details['data'][$cKey])) {
            unset($this->cm_row_details['data'][$cKey]);
        } else {
            $this->cm_row_details['data'][$cKey] = $bin;
        }
        $this->syncToSession();
    }

    public function selectAllBins()
    {
        foreach ($this->row_data['bin_data'] as $bin) {
            $this->cm_row_details['data'][$bin['composite_key']] = $bin;
        }
        $this->syncToSession();
    }

    public function clearAllBins()
    {
        $this->cm_row_details['data'] = [];
        $this->syncToSession();
    }

    protected function syncToSession()
    {
        $cm_details = Session::get('cm_details', []);
        $cm_details[$this->stockCode] = $this->cm_row_details;
        Session::put('cm_details', $cm_details);
    }

    protected function removeFromSession()
    {
        $cm_details = Session::get('cm_details', []);
        unset($cm_details[$this->stockCode]);
        Session::put('cm_details', $cm_details);
    }

    public function render()
    {
        return view('livewire.credit-memo.cm-row');
    }
}
