<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\Account;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;


class Create extends Component
{
    public $accounts;
    public $reasons;
    public $year, $month, $invoice_number, $account_id, $so_number, $po_number;
    public $cm_reason_id;
    public $invoice_data;
    public $detail_data;

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
    }

    public function selectSalesOrder($key) {
        $invoice = $this->invoice_data[$key];
        $account = Account::where('account_code', $invoice['Customer'])->first();
        $company = $account ? $account->company->name : null;

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

        $this->updateSession();
    }

    public function clearDetail() {
        $this->reset('detail_data');
        $this->reset(['so_number', 'invoice_number']);
    }

    public function updateSession() {
        $this->cm_data = [
            'account_id' => $this->account_id,
            'cm_reason_id' => $this->cm_reason_id,
            'invoice_number' => $this->invoice_number,
            'so_number' => $this->so_number,
            'po_number' => $this->po_number,
            'warehouse_location' => '',
            'ship_date' => '',
            'detail_data' => $this->detail_data,
        ];

        Session::put('cm_data', $this->cm_data);
    }

    public function saveRUD() {
        $this->validate([
            'cm_data.account_id' => [
                'required',
            ],
            'cm_data.invoice_number' => [
                'required',
            ],
            'cm_data.so_number' => [
                'required',
            ]
        ]);
    }
}
