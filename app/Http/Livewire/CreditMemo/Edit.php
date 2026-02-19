<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use App\Http\Traits\HandlesCreditMemo;
use App\Models\CreditMemo;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    use HandlesCreditMemo;

    public $rud;

    protected $listeners = [
        'accountSelected' => 'setAccount',
    ];

    public function mount(CreditMemo $credit_memo)
    {
        $this->rud = $credit_memo;
        $this->initializeCommonData();

        // 1. Restore Key Properties from DB
        $this->year = $this->rud->year;
        $this->month = $this->rud->month;
        $this->invoice_number = $this->rud->invoice_number;
        $this->so_number = $this->rud->so_number;
        $this->po_number = $this->rud->po_number;
        $this->account = $this->rud->account;
        $this->account_id = $this->rud->account_id;
        $this->cm_reason_id = $this->rud->credit_memo_reason_id;
        $this->cm_date = $this->rud->cm_date;

        // 2. Fetch the Invoice Lines from API (Fix for "No Data Loaded")
        $this->loadInvoiceDetails();

        // 3. Restore Selected Items into Session
        $this->restoreSessionFromDb();
    }

    public function setAccount($account_id)
    {
        $this->account_id = $account_id;
    }

    public function saveRUD($status)
    {
        $this->commonValidation();
        $this->saveToDatabase($this->rud, $status);

        return redirect()->route('cm.index')->with([
            'message_success' => 'Credit Memo Updated.',
        ]);
    }

    private function restoreSessionFromDb()
    {
        $cm_details = [];

        foreach($this->rud->cm_details as $detail) {
            $product = $detail->product;
            $stock = $product->stock_code;

            // Reconstruct the structure required by CmRow component
            $cm_details[$stock] = [
                'row_data' => [
                    'Warehouse' => $detail->warehouse,
                    'Bin' => $detail->bin, // Note: DB column names might be lowercase, View expects specific casing if using row_data directly.
                                           // Ideally CmRow logic should be case insensitive or mapped correctly here.
                    'warehouse' => $detail->warehouse, // keeping both casings safe
                    'order_quantity' => $detail->order_quantity,
                    'order_uom' => $detail->order_uom,
                    'price' => $detail->price,
                    'price_uom' => $detail->price_uom,
                    'unit_cost' => $detail->unit_cost,
                    'ship_quantity' => $detail->ship_quantity,
                    'stock_quantity_to_ship' => $detail->stock_quantity_to_ship,
                    'stocking_uom' => $detail->stocking_uom,
                    'line_ship_date' => $detail->line_ship_date,
                    // Add standard keys required by CmRow if they are missing from DB but present in API
                    'OrderQty' => $detail->order_quantity,
                    'OrderUom' => $detail->order_uom,
                    'StockingUom' => $detail->stocking_uom,
                ],
                'product' => $product,
                'data' => [],
            ];

            foreach($detail->cm_bins as $bin){
                $lot_bin_key = $bin->lot_number . '-' . $bin->bin;
                $cm_details[$stock]['data'][$lot_bin_key] = [
                    'Lot' => $bin->lot_number,
                    'Bin' => $bin->bin,
                    'composite_key' => $lot_bin_key, // Ensure consistency with CmRow
                    'conversion' => [
                        $bin->uom => $bin->quantity,
                    ],
                ];
            }
        }

        Session::put('cm_details', $cm_details);
        $this->saveSession(); // Restore cm_data header
    }

    public function render()
    {
        return view('livewire.credit-memo.edit');
    }
}
