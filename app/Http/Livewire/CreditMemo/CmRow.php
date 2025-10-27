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

    public function mount()
    {
        // Validate row_data to ensure required keys exist
        $this->validateRowData();

        // Fetch product once and cache
        $this->product = Product::where('stock_code', $this->row_data['StockCode'])->first();

        // Initialize cm_details from session
        $this->cm_details = Session::get('cm_details', []);

        // Check if details exist for this stock code
        if (isset($this->cm_details[$this->row_data['StockCode']])) {
            $this->cm_row_details = $this->cm_details[$this->row_data['StockCode']];
            $this->showDetail = 1;
        } else {
            $this->cm_row_details = ['data' => []];
            $this->showDetail = 0;
        }

        // Precompute UOM conversions for bin_data efficiently
        $this->precomputeConversions();
    }

    protected function validateRowData()
    {
        // Basic validation to prevent errors, similar to Create.php's validation
        if (!isset($this->row_data['StockCode'], $this->row_data['bin_data'])) {
            throw new \InvalidArgumentException('Invalid row_data: Missing required keys.');
        }
    }

    protected function precomputeConversions()
    {
        // Optimize UOM conversion by avoiding redundant calls
        foreach ($this->row_data['bin_data'] as $key => $bin) {
            $this->row_data['bin_data'][$key]['conversion'] = $this->uomConversion(
                $bin['StockQtyToShip'],
                $this->row_data['StockCode'],
                $this->row_data['StockingUom'],
                $this->row_data['OrderUom']
            );
        }
    }

    public function showDetails()
    {
        $this->showDetail = !$this->showDetail;
        $this->updateSession();
    }

    protected function updateSession()
    {
        // Batch session updates to optimize performance, following Create.php pattern
        $cm_details = Session::get('cm_details', []);

        if ($this->showDetail) {
            // Prepare row details data
            $this->cm_row_details['product'] = $this->product;
            $this->cm_row_details['row_data'] = [
                'warehouse' => $this->row_data['Warehouse'] ?? null,
                'bin' => $this->row_data['Bin'] ?? null,
                'order_quantity' => $this->row_data['OrderQty'] ?? null,
                'ship_quantity' => $this->row_data['ShipQty'] ?? null,
                'price' => $this->row_data['Price'] ?? null,
                'line_ship_date' => $this->row_data['LineShipDate'] ?? null,
                'unit_cost' => $this->row_data['UnitCost'] ?? null,
                'order_uom' => $this->row_data['OrderUom'] ?? null,
                'stock_quantity_to_ship' => $this->row_data['StockQtyToShip'] ?? null,
                'stocking_uom' => $this->row_data['StockingUom'] ?? null,
                'price_uom' => $this->row_data['PriceUom'] ?? null,
            ];

            // Initialize data if empty, using lot-bin keys to avoid duplicates
            if (empty($this->cm_row_details['data'])) {
                $this->cm_row_details['data'] = [];
                foreach ($this->row_data['bin_data'] as $bin) {
                    $lot_bin_key = $bin['Lot'] . '-' . $bin['Bin'];
                    $this->cm_row_details['data'][$lot_bin_key] = $bin;
                }
            }

            $cm_details[$this->row_data['StockCode']] = $this->cm_row_details;
        } else {
            unset($cm_details[$this->row_data['StockCode']]);
        }

        Session::put('cm_details', $cm_details);
    }

    public function selectBin($key)
    {
        // Ensure data array exists
        if (!isset($this->cm_row_details['data'])) {
            $this->cm_row_details['data'] = [];
        }

        if ($this->showDetail) {
            $lot_bin_key = $this->row_data['bin_data'][$key]['Lot'] . '-' . $this->row_data['bin_data'][$key]['Bin'];
            if (!isset($this->cm_row_details['data'][$lot_bin_key])) {
                // Add bin data if not present, using lot-bin key
                $this->cm_row_details['data'][$lot_bin_key] = $this->row_data['bin_data'][$key];
            } else {
                // Remove if already selected
                unset($this->cm_row_details['data'][$lot_bin_key]);
            }
        }

        $this->updateSession();
    }
}
