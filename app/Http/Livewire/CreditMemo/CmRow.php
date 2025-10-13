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
    public function showDetails() {
        if($this->showDetail) {
            $this->showDetail = 0;
        } else {
            $this->showDetail = 1;
        }

        $this->setSession();
    }

    public function setSession() {
        $this->cm_data = Session::get('cm_data');
        if($this->showDetail == 1) {
            $this->cm_data['cm_details'][$this->row_data['StockCode']] = $this->cm_row_details;
        } else {
            unset($this->cm_data['cm_details'][$this->row_data['StockCode']]);
        }
        Session::put('cm_data', $this->cm_data);
    }

    public function updatedCmRowDetails() {
        $this->setSession();
    }

    public function selectBin($key) {
        if(!empty($this->cm_data['cm_details'][$this->row_data['Sto~kCode']])) {
            $this->cm_row_details['bin_data'][$key] =$this->row_data['bin_data'][$key];
        }

        $this->setSession();
    }
}
