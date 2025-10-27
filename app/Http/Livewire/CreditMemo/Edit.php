<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\Account;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

use App\Models\CreditMemo;
use App\Models\CreditMemoDetail;
use App\Models\CreditMemoDetailBin;
use App\Models\CreditMemoApproval;

class Edit extends Component
{
    public $rud;

    public $accounts;
    public $reasons;
    public $year, $month, $invoice_number, $account, $account_id, $so_number, $po_number;
    public $cm_reason_id;
    public $cm_date;
    public $invoice_data;
    public $selected_invoice;
    public $detail_data;
    public $cm_data;
    public $cm_details;

    public $show_summary = false;

    protected $api_url = "192.168.11.240/refreshable/public/api/credit-memo/";

    public function render()
    {
        return view('livewire.credit-memo.edit');
    }

    public function mount($credit_memo) {
        $this->rud = $credit_memo;

        $this->year = $this->rud->year;
        $this->month = $this->rud->month;
        $this->invoice_number = $this->rud->invoice_number;
        $this->so_number = $this->rud->so_number;
        $this->po_number = $this->rud->po_number;
        $this->account = $this->rud->account;

        $this->account_id = $this->rud->account_id;
        $this->cm_reason_id = $this->rud->credit_memo_reason_id;
        $this->cm_date = $this->rud->cm_date;

        // Cache static data to avoid redundant queries
        $this->accounts = Cache::remember('accounts_list', 3600, function () {
            return Account::orderBy('account_code', 'ASC')->get();
        });
        $this->reasons = Cache::remember('credit_memo_reasons', 3600, function () {
            return CreditMemoReason::orderBy('reason_code', 'DESC')->get();
        });

        $this->getInvoice();
        $this->initializeSessionData();
    }

    protected function initializeSessionData()
    {
        $this->cm_data = Session::get('cm_data', []);
        if (empty($this->cm_data)) {
            $this->cm_data = [
                'account' => $this->account,
                'account_id' => $this->account_id,
                'cm_reason_id' => $this->cm_reason_id,
                'invoice_number' => $this->invoice_number,
                'so_number' => $this->so_number,
                'po_number' => '',
                'warehouse_location' => '',
                'ship_date' => '',
            ];
            Session::put('cm_data', $this->cm_data);
        }

        $cm_details = Session::get('cm_details', []);
        if (empty($cm_details)) {
            $cm_details = [];
            foreach($this->rud->cm_details as $detail) {
                $product = $detail->product;
                $cm_details[$product->stock_code] = [
                    'row_data' => [
                        'warehouse' => $detail->warehouse,
                        'bin' => $detail->bin,
                        'order_quantity' => $detail->order_quantity,
                        'order_uom' => $detail->order_uom,
                        'price' => $detail->price,
                        'price_uom' => $detail->price_uom,
                        'unit_cost' => $detail->unit_cost,
                        'ship_quantity' => $detail->ship_quantity,
                        'stock_quantity_to_ship' => $detail->stock_quantity_to_ship,
                        'stocking_uom' => $detail->stocking_uom,
                        'line_ship_date' => $detail->line_ship_date,
                    ],
                    'product' => $product,
                    'data' => [],
                ];

                foreach($detail->cm_bins as $bin){
                    $lot_bin_key = $bin->lot_number . '-' . $bin->bin;
                    $cm_details[$product->stock_code]['data'][$lot_bin_key] = [
                        'Lot' => $bin->lot_number,
                        'Bin' => $bin->bin,
                        'conversion' => [
                            $bin->uom => $bin->quantity,
                        ],
                    ];
                }
            }

            Session::put('cm_details', $cm_details);
        }
    }

    public function searchInvoice()
    {
        try {
            $account = Account::find($this->account_id);
            if (!$account) {
                $this->addError('account_id', 'Invalid account selected.');
                return;
            }
            $company = $account->company->name ?? null;

            $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . env('API_TOKEN_SYSPRODATA'),
                    'year' => $this->year,
                    'month' => $this->month,
                    'invoice_number' => $this->invoice_number,
                    'company' => $company,
                    'so_number' => $this->so_number,
                    'po_number' => $this->po_number,
                    'account_code' => $account->account_code,
                ])
                ->timeout(30)
                ->get($this->api_url . 'getInvoice');

            if ($response->failed()) {
                $this->addError('search', 'Failed to fetch invoice data. Please try again.');
                return;
            }

            $this->invoice_data = $response->json();
            $this->reset('detail_data');
            $this->show_summary = false;

            Session::forget(['cm_data', 'cm_details']);
        } catch (\Exception $e) {
            $this->addError('search', 'An error occurred while searching for invoices.');
        }
    }

    public function selectSalesOrder($key)
    {
        try {
            if (!isset($this->invoice_data[$key])) {
                $this->addError('select', 'Invalid invoice selection.');
                return;
            }

            $invoice = $this->invoice_data[$key];
            $this->account = Account::where('account_code', $invoice['Customer'])->first();
            if (!$this->account) {
                $this->addError('select', 'Account not found for the selected invoice.');
                return;
            }
            $company = $this->account->company->name ?? null;

            $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . env('API_TOKEN_SYSPRODATA'),
                    'year' => $invoice['TrnYear'],
                    'month' => $invoice['TrnMonth'],
                    'invoice_number' => $invoice['InvoiceNumber'],
                    'company' => $company,
                    'sales_order' => $invoice['SalesOrder'],
                    'account_code' => $invoice['Customer'],
                ])
                ->timeout(30)
                ->get($this->api_url . 'getInvoiceDetail');

            if ($response->failed()) {
                $this->addError('select', 'Failed to fetch invoice details. Please try again.');
                return;
            }

            $this->detail_data = $response->json();
            $this->so_number = $invoice['SalesOrder'];
            $this->invoice_number = $invoice['InvoiceNumber'];
            $this->po_number = $invoice['CustomerPoNumber'];
            $this->selected_invoice = $invoice;

            $this->saveSession();
        } catch (\Exception $e) {
            $this->addError('select', 'An error occurred while selecting the sales order.');
        }
    }

    public function clearDetail()
    {
        $this->reset(['detail_data', 'so_number', 'invoice_number', 'selected_invoice']);
        Session::forget(['cm_data', 'cm_details']);
    }

    protected function saveSession()
    {
        $this->cm_data = [
            'account' => $this->account,
            'account_id' => $this->account->id,
            'cm_reason_id' => $this->cm_reason_id,
            'invoice_number' => $this->invoice_number,
            'so_number' => $this->so_number,
            'po_number' => $this->po_number,
            'warehouse_location' => '',
            'ship_date' => '',
            'detail_data' => $this->detail_data,
            'ship_code' => null,
            'ship_name' => $this->selected_invoice['CustomerName'] ?? null,
            'shipping_instruction' => null,
            'ship_address1' => $this->selected_invoice['ShipAddr1'] ?? null,
            'ship_address2' => $this->selected_invoice['ShipAddr2'] ?? null,
            'ship_address3' => $this->selected_invoice['ShipAddr3'] ?? null,
            'ship_address4' => $this->selected_invoice['ShipAddr4'] ?? null,
            'ship_address5' => $this->selected_invoice['ShipAddr5'] ?? null,
        ];

        Session::put('cm_data', $this->cm_data);
    }

    public function showSummary()
    {
        $this->show_summary = !$this->show_summary;
        $this->cm_details = Session::get('cm_details', []);
    }

    public function getInvoice() {
        try {
            $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . env('API_TOKEN_SYSPRODATA'),
                    'year' => $this->year,
                    'month' => $this->month,
                    'invoice_number' => $this->invoice_number,
                    'company' => $this->account->company->name ?? NULL,
                    'sales_order' => $this->so_number,
                    'account_code' => $this->account->account_code ?? NULL,
                    'po_number' => $this->po_number,
                ])
                ->timeout(30)
                ->get($this->api_url . 'getInvoiceData');

            if ($response->failed()) {
                $this->addError('select', 'Failed to fetch invoice details. Please try again.');
                return;
            }
            $data = $response->json();

            $this->detail_data = $data['details'] ?? [];
            $this->so_number = $data['SalesOrder'];
            $this->invoice_number = $data['InvoiceNumber'];
            $this->po_number = $data['CustomerPoNumber'];
            $this->selected_invoice = $data;

            $this->saveSession();
        } catch(\Exception $e) {
            $this->addError('get invoice', 'An error occurred while extracting the so invoice data.');
        }
    }

    public function saveRUD($status)
    {
        $this->validate([
            'cm_data.account_id' => 'required|exists:accounts,id',
            'cm_data.cm_reason_id' => 'required|exists:credit_memo_reasons,id',
            'cm_data.invoice_number' => 'required|string',
            'cm_data.so_number' => 'required|string',
            'cm_data.po_number' => 'required|string',
            'cm_details' => 'required|array|min:1',
        ], [
            'cm_data.account_id.required' => 'Account is required.',
            'cm_data.cm_reason_id.required' => 'Credit memo reason is required.',
            'cm_data.invoice_number.required' => 'Invoice number is required.',
            'cm_data.so_number.required' => 'Sales order number is required.',
            'cm_data.po_number.required' => 'PO number is required.',
            'cm_details.required' => 'At least one item must be selected.',
        ]);

        $this->rud->update([
            'account_id' => $this->cm_data['account_id'],
            'credit_memo_reason_id' => $this->cm_data['cm_reason_id'] ?? NULL,
            'invoice_number' => $this->cm_data['invoice_number'],
            'po_number' => $this->cm_data['po_number'],
            'so_number' => $this->cm_data['so_number'],
            'ship_date' => $this->cm_date,
            'ship_code' => $this->cm_data['ship_code'],
            'ship_name' => $this->cm_data['ship_name'],
            'shipping_instruction' => $this->cm_data['shipping_instruction'],
            'ship_address1' => $this->cm_data['ship_address1'],
            'ship_address2' => $this->cm_data['ship_address2'],
            'ship_address3' => $this->cm_data['ship_address3'],
            'ship_address4' => $this->cm_data['ship_address4'],
            'ship_address5' => $this->cm_data['ship_address5'],
            'status' => $status
        ]);

        // Delete existing details and bins
        $this->rud->cm_details()->each(function ($detail) {
            $detail->cm_bins()->forceDelete();
        });
        $this->rud->cm_details()->forceDelete();

        foreach($this->cm_details as $stock_code => $detail) {
            $product = $detail['product'];

            if(!empty($product)) {
                $rud_detail = new CreditMemoDetail([
                    'credit_memo_id' => $this->rud->id,
                    'product_id' => $product['id'],
                    'credit_note_number' => NULL,
                    'warehouse' => $detail['row_data']['warehouse'] ?? NULL,
                    'order_quantity' => $detail['row_data']['order_quantity'] ?? NULL,
                    'order_uom' => $detail['row_data']['order_uom'] ?? NULL,
                    'price' => $detail['row_data']['price'] ?? NULL,
                    'price_uom' => $detail['row_data']['price_uom'] ?? NULL,
                    'unit_cost' => $detail['row_data']['unit_cost'] ?? NULL,
                    'ship_quantity' => $detail['row_data']['ship_quantity'] ?? NULL,
                    'stock_quantity_to_ship' => $detail['row_data']['stock_quantity_to_ship'] ?? NULL,
                    'stocking_uom' => $detail['row_data']['stocking_uom'] ?? NULL,
                ]);
                $rud_detail->save();

                foreach($detail['data'] as  $bin_data) {
                    foreach($bin_data['conversion'] as $uom => $conv) {
                        $rud_detail_bin = new CreditMemoDetailBin([
                            'credit_memo_detail_id' => $rud_detail->id,
                            'lot_number' => $bin_data['Lot'],
                            'bin' => $bin_data['Bin'],
                            'quantity' => $conv,
                            'uom' => $uom,
                        ]);
                        $rud_detail_bin->save();
                    }
                }
            }
        }

        if($status == 'submitted') {
            $approval = new CreditMemoApproval([
                'credit_memo_id' => $this->rud->id,
                'user_id' => auth()->user()->id,
                'status' => $status,
                'remarks' => NULL,
            ]);
            $approval->save();

            // logs
            activity('submitted')
                ->performedOn($this->rud)
                ->log(':causer.firstname :causer.lastname has submitted RUD');
        } else {
            // logs
            activity('update')
                ->performedOn($this->rud)
                ->log(':causer.firstname :causer.lastname has updated RUD');
        }

        return redirect()->route('cm.index')->with([
            'message_success' => 'Credit Memo successfully saved.',
        ]);
    }
}
