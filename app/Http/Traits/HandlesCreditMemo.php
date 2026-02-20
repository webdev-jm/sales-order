<?php

namespace App\Http\Traits;

use App\Models\Account;
use App\Models\CreditMemo;
use App\Models\CreditMemoDetail;
use App\Models\CreditMemoDetailBin;
use App\Models\CreditMemoApproval;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait HandlesCreditMemo
{
    public $accounts;
    public $reasons;
    public $year, $month, $invoice_number, $account, $account_id, $so_number, $po_number;
    public $cm_reason_id;
    public $cm_date;
    public $invoice_data;
    public $selected_invoice;
    public $detail_data = []; // Initialize as array
    public $cm_data;
    public $cm_details;
    public $show_summary = false;

    protected $api_base_url = '192.168.11.240/refreshable/public/api/credit-memo/';

    public function initializeCommonData()
    {
        $this->accounts = Cache::remember('accounts_list', 3600, fn() => Account::orderBy('account_code', 'ASC')->get());
        $this->reasons = Cache::remember('credit_memo_reasons', 3600, fn() => CreditMemoReason::orderBy('reason_code', 'DESC')->get());

        if (!$this->year) $this->year = date('Y');
        if (!$this->month) $this->month = (int)date('m');
    }

    /**
     * Centralized method to fetch invoice lines from API.
     * Used by Create (via selectSalesOrder) and Edit (via mount).
     */
    public function loadInvoiceDetails()
    {
        // Ensure we have an account object
        if (!$this->account && $this->account_id) {
            $this->account = Account::find($this->account_id);
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . env('API_TOKEN_SYSPRODATA'),
                'year' => $this->year,
                'month' => $this->month,
                'invoice_number' => $this->invoice_number,
                'company' => $this->account->company->name ?? null,
                'sales_order' => $this->so_number,
                'account_code' => $this->account->account_code ?? null,
                'po_number' => $this->po_number,
            ])->timeout(30)->get($this->api_base_url . 'getInvoiceData');

            if ($response->failed()) {
                $this->addError('load_details', 'Failed to fetch invoice details from Syspro.');
                return;
            }

            $data = $response->json();

            $this->detail_data = $data['details'] ?? [];
            $this->so_number = $data['SalesOrder'];
            $this->invoice_number = $data['InvoiceNumber'];
            $this->po_number = $data['CustomerPoNumber'];
            $this->selected_invoice = $data;

        } catch (\Exception $e) {
            $this->addError('load_details', 'Connection error: ' . $e->getMessage());
        }
    }

    public function searchInvoice()
    {
        $this->validate(['account_id' => 'required', 'year' => 'required']);

        try {
            $this->account = Account::find($this->account_id);
            if (!$this->account) return $this->addError('account_id', 'Invalid account.');

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . env('API_TOKEN_SYSPRODATA'),
                'year' => $this->year,
                'month' => $this->month,
                'invoice_number' => $this->invoice_number,
                'company' => $this->account->company->name ?? null,
                'so_number' => $this->so_number,
                'po_number' => $this->po_number,
                'account_code' => $this->account->account_code,
            ])->timeout(30)->get($this->api_base_url . 'getInvoice');

            if ($response->failed()) return $this->addError('search', 'Failed to fetch invoice data.');

            $this->invoice_data = $response->json();
            $this->reset('detail_data');
            $this->show_summary = false;
            Session::forget(['cm_data', 'cm_details']);

        } catch (\Exception $e) {
            $this->addError('search', 'Connection error: ' . $e->getMessage());
        }
    }

    public function selectSalesOrder($key)
    {
        if (!isset($this->invoice_data[$key])) return;

        $invoice = $this->invoice_data[$key];

        // Set properties needed for loadInvoiceDetails
        $this->account = Account::where('account_code', $invoice['Customer'])->first();
        $this->so_number = $invoice['SalesOrder'];
        $this->invoice_number = $invoice['InvoiceNumber'];
        $this->po_number = $invoice['CustomerPoNumber'];
        $this->year = $invoice['TrnYear'];
        $this->month = $invoice['TrnMonth'];
        $this->selected_invoice = $invoice;

        // Call the centralized method
        $this->loadInvoiceDetails();

        $this->saveSession();
    }

    // ... (rest of the trait: showSummary, saveToDatabase, etc. remain the same) ...
    public function showSummary() {
        $this->show_summary = !$this->show_summary;
        $this->cm_details = Session::get('cm_details', []);
    }

    public function clearDetail() {
        $this->reset(['detail_data', 'so_number', 'invoice_number', 'selected_invoice']);
        Session::forget(['cm_data', 'cm_details']);
    }

    protected function commonValidation() {
        $this->validate([
            'cm_data.account_id' => 'required',
            'cm_data.cm_reason_id' => 'required',
            'cm_details' => 'required|array|min:1',
        ]);
    }

    public function updateQuantity($stockCode, $binKey, $qty) {
        if (!isset($this->cm_details[$stockCode]['data'][$binKey])) {
            return;
        }

        // 2. Get the UOM key (e.g., 'EA', 'KG')
        $conversion = $this->cm_details[$stockCode]['data'][$binKey]['conversion'];
        $uom = array_key_first($conversion); // Helper to get the first key (the UOM)

        // 3. Update the quantity for that specific bin
        $this->cm_details[$stockCode]['data'][$binKey]['conversion'][$uom] = (float) $qty;

        // 4. (Optional) Auto-update the Header 'Order Quantity' to match the sum of bins
        $newTotal = 0;
        foreach($this->cm_details[$stockCode]['data'] as $bin) {
             $newTotal += array_values($bin['conversion'])[0];
        }
        $this->cm_details[$stockCode]['row_data']['OrderQty'] = $newTotal;
        $this->cm_details[$stockCode]['row_data']['order_quantity'] = $newTotal;

        // 5. Sync to Session so the change persists if we reload or submit
        Session::put('cm_details', $this->cm_details);
    }

    public function updateUom($stockCode, $binKey, $newUom)
    {
        if (empty($newUom) || !isset($this->cm_details[$stockCode]['data'][$binKey])) {
            return;
        }

        // 1. Get the current quantity value (we want to preserve the number)
        $currentConversion = $this->cm_details[$stockCode]['data'][$binKey]['conversion'];
        $currentQty = reset($currentConversion); // Get the first value (quantity)

        // 2. Rebuild the conversion array with the NEW UOM as the key
        $this->cm_details[$stockCode]['data'][$binKey]['conversion'] = [
            $newUom => $currentQty
        ];

        // 3. Update the OrderUom for the main row as well (optional, keeps data consistent)
        // Only do this if you want the main row UOM to match the last edited bin UOM
        $this->cm_details[$stockCode]['row_data']['OrderUom'] = $newUom;
        $this->cm_details[$stockCode]['row_data']['order_uom'] = $newUom;

        // 4. Sync to Session
        Session::put('cm_details', $this->cm_details);
    }

    protected function saveSession() {
        $this->cm_data = [
            'account' => $this->account,
            'account_id' => $this->account->id ?? $this->account_id,
            'cm_reason_id' => $this->cm_reason_id,
            'invoice_number' => $this->invoice_number,
            'so_number' => $this->so_number,
            'po_number' => $this->po_number,
            'year' => $this->year,
            'month' => $this->month,
            'ship_name' => $this->selected_invoice['CustomerName'] ?? ($this->cm_data['ship_name'] ?? NULL),
            'ship_address1' => $this->selected_invoice['ShipAddr1'] ?? ($this->cm_data['ship_address1'] ?? NULL),
            'ship_address2' => $this->selected_invoice['ShipAddr2'] ?? ($this->cm_data['ship_address2'] ?? NULL),
            'ship_address3' => $this->selected_invoice['ShipAddr3'] ?? ($this->cm_data['ship_address3'] ?? NULL),
            'ship_address4' => $this->selected_invoice['ShipAddr4'] ?? ($this->cm_data['ship_address4'] ?? NULL),
            'ship_address5' => $this->selected_invoice['ShipAddr5'] ?? ($this->cm_data['ship_address5'] ?? NULL),
        ];
        Session::put('cm_data', $this->cm_data);
    }

    protected function saveToDatabase($rud, $status) {
         DB::transaction(function () use ($rud, $status) {
            $rud->fill([
                'account_id' => $this->cm_data['account_id'],
                'user_id' => auth()->id(),
                'credit_memo_reason_id' => $this->cm_data['cm_reason_id'] ?? null,
                'invoice_number' => $this->cm_data['invoice_number'],
                'po_number' => $this->cm_data['po_number'],
                'so_number' => $this->cm_data['so_number'],
                'year' => (int)$this->cm_data['year'],
                'month' => (int)$this->cm_data['month'],
                'cm_date' => now(),
                'ship_date' => $this->cm_date,
                'status' => $status,
                'ship_name' => $this->cm_data['ship_name'] ?? null,
                'ship_address1' => $this->cm_data['ship_address1'] ?? null,
                'ship_address2' => $this->cm_data['ship_address2'] ?? null,
                'ship_address3' => $this->cm_data['ship_address3'] ?? null,
                'ship_address4' => $this->cm_data['ship_address4'] ?? null,
                'ship_address5' => $this->cm_data['ship_address5'] ?? null,
            ]);
            $rud->save();

            $rud->cm_details()->each(fn($d) => $d->cm_bins()->delete());
            $rud->cm_details()->delete();

            foreach ($this->cm_details as $detail) {
                if (empty($detail['product'])) continue;
                $rudDetail = $rud->cm_details()->create([
                    'product_id' => $detail['product']['id'],
                    'warehouse' => $detail['row_data']['warehouse'] ?? null,
                    'order_quantity' => $detail['row_data']['order_quantity'] ?? 0,
                    'order_uom' => $detail['row_data']['order_uom'] ?? null,
                    'price' => $detail['row_data']['price'] ?? 0,
                    'price_uom' => $detail['row_data']['price_uom'] ?? null,
                    'unit_cost' => $detail['row_data']['unit_cost'] ?? 0,
                    'ship_quantity' => $detail['row_data']['ship_quantity'] ?? 0,
                    'stock_quantity_to_ship' => $detail['row_data']['stock_quantity_to_ship'] ?? 0,
                    'stocking_uom' => $detail['row_data']['stocking_uom'] ?? null,
                ]);

                foreach ($detail['data'] as $binData) {
                    foreach ($binData['conversion'] ?? [] as $uom => $qty) {
                        $rudDetail->cm_bins()->create([
                            'lot_number' => $binData['Lot'],
                            'bin' => $binData['Bin'],
                            'quantity' => $qty,
                            'uom' => $uom,
                        ]);
                    }
                }
            }

            // Only create approval if submitted, or just log update
             if($status == 'submitted') {
                CreditMemoApproval::create([
                    'credit_memo_id' => $rud->id,
                    'user_id' => auth()->id(),
                    'status' => $status,
                ]);
             }

            activity($status == 'submitted' ? 'submitted' : 'updated')
                ->performedOn($rud)
                ->log(":causer.firstname has {$status} RUD");
        });
    }
}
