<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\Account;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function selectSalesOrder($invoice_number, $so_number, $account_code, $year, $month) {
        $account = Account::where('account_code', $account_code)->first();
        $company = $account ? $account->company->name : null;

        $response = Http::withHeaders([
            'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'year' => $year,
                'month' => $month,
                'invoice_number' => $invoice_number,
                'company' => $company,
                'sales_order' => $so_number,
                'account_code' => $account_code ?? '',
        ])
        ->get($this->api_url.'getInvoiceDetail');

        $this->detail_data = $response->json();
    }

    public function clearDetail() {
        $this->reset('detail_data');
    }
}
