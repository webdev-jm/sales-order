<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\Account;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;

class Create extends Component
{
    public $accounts;
    public $reasons;
    public $year, $month, $invoice_number, $account_id, $cm_reason_id;
    public $invoice_data;

    public $api_url = "192.168.11.240/refreshable/public/api/credit-memo/invoice";

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
                'Authorization' => 'Bearer UaHxtws9LHZ47QG21lBXjQgka3Fe93H5xV1Y6HBQDN4=',
                'year' => $this->year,
                'month' => $this->month,
                'invoice_number' => $this->invoice_number,
                'company' => $company,
            ])
            ->get($this->api_url);

        $this->invoice_data = $response->json();

        dd($this->invoice_data);
    }
}
