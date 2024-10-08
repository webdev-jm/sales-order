<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\PafPrePlan;
use App\Models\PafExpenseType;
use App\Models\PafActivity;

use Illuminate\Support\Facades\Session;

class PrePlan extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $selected = null;

    public function select($pre_plan_id) {
        $this->selected = PafPrePlan::find($pre_plan_id);
    }

    public function selectPrePlan() {
        $paf_data = Session::get('paf_data');
        
        $header = [
            'pre_plan_number' => $this->selected->pre_plan_number,
            'account' => $this->selected->account,
            'support_type' => $this->selected->support_type ?? NULL,
            'expense_type' => PafExpenseType::where('expense', 'BUDGETED')->first(),
            'title' => $this->selected->title,
            'start_date' => $this->selected->start_date,
            'end_date' => $this->selected->end_date,
            'activity' => $this->selected->activity ?? NULL,
        ];

        $details = [];
        foreach($this->selected->pre_plan_details as $detail) {
            $product = $detail->product;
            $details[] = [
                'type' => $detail->type,
                'quantity' => $detail->quantity,
                'srp' => '',
                'percentage' => '',
                'amount' => $detail->amount,
                'expense' => '',
                'product_id' => $detail->product_id,
                'product' => ($product->stock_code ?? '').' - '.($product->description ?? '').($product->size ?? ''),
                'branch' => $detail->branch
            ];
        }

        $paf_data['header'] = $header;
        $paf_data['details'] = $details;

        Session::put('paf_data', $paf_data);

        $this->emit('setPrePlan');
        $this->emit('closeModalPrePlan');
    }
    
    public function render()
    {
        $pre_plans = PafPrePlan::orderBy('pre_plan_number', 'DESC')
            ->whereNull('paf_id')
            ->paginate(15, ['*'], 'pre-plan-page')
            ->onEachSide(1);

        return view('livewire.paf.pre-plan')->with([
            'pre_plans' => $pre_plans
        ]);
    }
}
