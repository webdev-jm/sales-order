<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\Account;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

use App\Models\CreditMemo;
use App\Models\CreditMemoDetail;
use App\Models\CreditMemoDetailBin;
use App\Models\Product;

class Create extends Component
{
    public $accounts;
    public $reasons;
    public $year, $month, $invoice_number, $account, $account_id, $so_number, $po_number;
    public $cm_reason_id;
    public $invoice_data;
    public $selected_invoice;
    public $detail_data;
    public $cm_data;
    public $cm_details;

    public $show_summary = false;

    public $api_url = "192.168.11.240/refreshable/public/api/credit-memo/";

    public function render()
    {
        return view('livewire.credit-memo.create');
    }

    public function mount() {
        $this->accounts = Account::orderBy('account_code', 'ASC')->get();
        $this->reasons = CreditMemoReason::orderBy('reason_code', 'DESC')->get();

        $this->year = date('Y');
        $this->month = (int)date('m');

        $this->cm_data = Session::get('cm_data');
        if(empty($this->cm_data)) {
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
    }

    public function searchInvoice() {
        $account = Account::find($this->account_id);
        $company = $account ? $account->company->name : null;

        $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'year' => $this->year,
                'month' => $this->month,
                'invoice_number' => $this->invoice_number,
                'company' => $company,
                'so_number' => $this->so_number,
                'po_number' => $this->po_number,
                'account_code' => $account->account_code ?? '',
            ])
            ->get($this->api_url.'getInvoice');

        $this->invoice_data = $response->json();

        $this->reset('detail_data');
        $this->$this->show_summary = false;

        Session::forget('cm_data');
        Session::forget('cm_details');
    }

    public function selectSalesOrder($key) {
        $invoice = $this->invoice_data[$key];
        $this->account = Account::where('account_code', $invoice['Customer'])->first();
        $company = $this->account ? $this->account->company->name : null;

        $response = Http::withHeaders([
            'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'year' => $invoice['TrnYear'],
                'month' => $invoice['TrnMonth'],
                'invoice_number' => $invoice['InvoiceNumber'],
                'company' => $company,
                'sales_order' => $invoice['SalesOrder'],
                'account_code' => $invoice['Customer'] ?? '',
        ])
        ->get($this->api_url.'getInvoiceDetail');

        $this->detail_data = $response->json();

        $this->so_number = $invoice['SalesOrder'];
        $this->invoice_number = $invoice['InvoiceNumber'];
        $this->po_number = $invoice['CustomerPoNumber'];

        $this->selected_invoice = $invoice;

        $this->saveSession();
    }

    public function clearDetail() {
        $this->reset('detail_data');
        $this->reset(['so_number', 'invoice_number']);

        Session::forget('cm_data');
        Session::forget('cm_details');
    }

    public function saveSession() {
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
            'ship_code' =>  NULL,
            'ship_name' => $this->selected_invoice['CustomerName'] ?? NULL,
            'shipping_instruction' => NULL,
            'ship_address1' => $this->selected_invoice['ShipAddr1'] ?? NULL,
            'ship_address2' => $this->selected_invoice['ShipAddr2'] ?? NULL,
            'ship_address3' => $this->selected_invoice['ShipAddr3'] ?? NULL,
            'ship_address4' => $this->selected_invoice['ShipAddr4'] ?? NULL,
            'ship_address5' => $this->selected_invoice['ShipAddr5'] ?? NULL,
        ];

        Session::put('cm_data', $this->cm_data);
    }

    public function showSummary() {
        if($this->show_summary) {
            $this->show_summary = false;
        } else {
            $this->show_summary = true;
        }

        $this->cm_details = Session::get('cm_details');
    }

    public function saveRUD($status) {
        $this->validate([
            'cm_data.account_id' => [
                'required'
            ],
            'cm_data.cm_reason_id' => [
                'required'
            ],
            'cm_data.invoice_number' => [
                'required'
            ],
            'cm_data.so_number' => [
                'required'
            ],
            'cm_data.po_number' => [
                'required'
            ],
        ]);

        $rud = new CreditMemo([
            'account_id' => $this->cm_data['account_id'],
            'user_id' => auth()->user()->id,
            'credit_memo_reason_id' => $this->cm_data['cm_reason_id'] ?? NULL,
            'invoice_number' => $this->cm_data['invoice_number'],
            'po_number' => $this->cm_data['po_number'],
            'so_number' => $this->cm_data['so_number'],
            'cm_date' => now(),
            'ship_date' => $this->cm_data['ship_date'],
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
        $rud->save();

        foreach($this->cm_details as $detail) {
            $product = $detail['product'];

            if(!empty($product)) {
                $rud_detail = new CreditMemoDetail([
                    'credit_memo_id' => $rud->id,
                    'product_id' => $product->id,
                    'quantity' => $detail['StockQtyToShip'],
                    'uom' => $detail['UOM'],
                ]);
                $rud_detail->save();

                foreach($detail['conversion'] as $uom => $conv) {
                    $rud_detail_bin = new CreditMemoDetailBin([
                        'credit_memo_detail_id' => $rud_detail->id,
                        'lot_number' => $detail['Lot'],
                        'bin_location' => $detail['BinLocation'],
                        'quantity' => $conv,
                        'uom' => $uom,
                    ]);
                    $rud_detail_bin->save();
                }
            }
        }
    }

}
