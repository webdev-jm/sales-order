<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CmRow extends Component
{
    public $row_data;
    public $showDetail;
    public $cm_data;
    public $cm_row_details;
    public function render()
    {
        return view('livewire.credit-memo.cm-row');
    }

    public function mount() {
        $this->cm_data = Session::get('cm_data');
    }

    public function showDetails() {
        if($this->showDetail) {
            $this->showDetail = 0;
        } else {
            $this->showDetail = 1;
        }
    }

    public function setSession() {
        $this->cm_data['cm_details'][$this->row_data['StockCode']] = $this->cm_row_details;
        Session::put('cm_data', $this->cm_data);
    }
}
