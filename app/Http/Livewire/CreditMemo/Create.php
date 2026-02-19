<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use App\Http\Traits\HandlesCreditMemo;
use App\Models\CreditMemo;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    use HandlesCreditMemo;

    protected $listeners = ['accountSelected' => 'setAccount'];

    public function mount()
    {
        $this->initializeCommonData();

        // Initialize Session
        $this->cm_data = Session::get('cm_data', []);
        if (empty($this->cm_data)) {
            $this->saveSession(); // Save defaults
        } else {
            // Restore state if session exists
            $this->account_id = $this->cm_data['account_id'] ?? null;
            $this->year = $this->cm_data['year'] ?? date('Y');
        }
    }

    public function setAccount($account_id)
    {
        $this->account_id = $account_id;
    }

    public function saveRUD($status)
    {
        $this->commonValidation();
        $rud = new CreditMemo();
        $this->saveToDatabase($rud, $status);

        return redirect()->route('cm.index')->with('message_success', 'Credit Memo Created.');
    }

    public function render()
    {
        return view('livewire.credit-memo.create');
    }
}
