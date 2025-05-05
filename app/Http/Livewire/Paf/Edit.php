<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

use App\Models\Paf;
USE App\Models\PafDetail;
use App\Models\PafActivity;
use App\Models\PafSupportType;
use App\Models\PafExpenseType;
use App\Models\Product;

use Carbon\Carbon;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class Edit extends Component
{
    public $paf;

    public $accounts = [];
    public $support_types = [];
    public $expense_types = [];
    public $activities = [];
    public $products = [];
    public $branches = [];

    public $days = 30;

    public $account_id, $support_type_id, $expense_type_id, $activity_id;
    public $title, $program_start, $program_end, $pre_plan_number;
    public $details = [];

    public function savePaf($status) {
        $this->validate([
            'account_id' => [
                'required'
            ],
            'support_type_id' => [
                'required'
            ],
            'expense_type_id' => [
                'required'
            ],
            'title' => [
                'required'
            ],
            'program_start' => [
                'required'
            ],
            'program_end' => [
                'required'
            ]
        ]);
        
        $type = $this->checkPafType($this->program_start);

        $this->paf->update([
            'account_id' => $this->account_id,
            'paf_expense_type_id' => $this->expense_type_id,
            'paf_support_type_id' => $this->support_type_id,
            'paf_activity_id' => $this->activity_id ?? NULL,
            'title' => $this->title,
            'start_date' => $this->program_start,
            'end_date' => $this->program_end,
            'status' => $status,
            'concept' => ''
        ]);

        if(!empty($this->details)) {
            $this->paf->paf_details()->forceDelete();
            foreach($this->details as $val) {
                if(!empty($val['product_id']) && !empty($val['quantity'])) {
                    $paf_detail = new PafDetail([
                        'paf_id' => $this->paf->id,
                        'product_id' => $val['product_id'],
                        'branch_id' => $val['branch_id'] ?? NULL,
                        'branch' => $val['branch'],
                        'amount' => empty($val['amount']) ? 0 : $val['amount'],
                        'expense' => empty($val['expense']) ? 0 : $val['expense'],
                        'quantity' => empty($val['quantity']) ? 0 : $val['quantity'],
                        'srp' => empty($val['srp']) ? 0 : $val['srp'],
                        'percentage' => empty($val['percentage']) ? 0 : $val['percentage'],
                        'status' => NULL,
                    ]);
                    $paf_detail->save();
                }
            }
        }

        // update pre plan
        if(!empty($this->pre_plan_number)) {
            $curr_pre_plan = $this->paf->pre_plan;
            $pre_plan = PafPrePlan::where('pre_plan_number', $this->pre_plan_number)
                ->first();
            if(!empty($curr_pre_plan) && $curr_pre_plan->id != $pre_plan->id) {
                $curr_pre_plan->update([
                    'paf_id' => NULL
                ]);

                $pre_plan->update([
                    'paf_id' => $this->paf->id
                ]);
            }
        }

        if($status == 'submitted') {
            // record approvals
            $approval = new PafApproval([
                'paf_id' => $this->paf->id,
                'user_id' => auth()->user()->id,
                'status' => $status,
                'remarks' => NULL
            ]);
            $approval->save();

            // notify immediate superior 
            $superior_id = $this->paf->user->getImmediateSuperiorId();
            if(!empty($superior_id)) {
                $user = User::findOrFail($superior_id);
                if(!empty($user)) {
                    try {
                        Notification::send($user, new PafSubmitted($this->paf));
                    } catch(\Exception $e) {
                        Log::error('Notification failed: '.$e->getMessage());
                    }
                }
            }
        }

        session()->flash('message_success', 'PAF '.$paf->paf_number.' has been created.');
        
        return redirect()->route('paf.index');
    }

    private function checkPafType($start_date) {
        $type = 'D';
        // check type
        if(date('Y-m-d') > $start_date) {
            $type = 'P';
            return $type;
        }

        $curr_date = Carbon::parse(date('Y-m-d'));
        $start_date = Carbon::parse($start_date);
        $days_diff = $curr_date->diffInDays($start_date);
        if($days_diff <= $this->days) { // LATE
            $type = 'S';
        } else { // DATED
            $type = 'D';
        }

        return $type;
    }

    public function updatedAccountId() {
        $account  = $this->accounts->where('id', $this->account_id)->first();
        $this->activities = PafActivity::where('company_id', $account->company_id)->get();
        $this->branches = $account->branches;
        $this->products = Product::whereHas('price_codes', function($query) use($account) {
            $query->where('company_id', $account->company_id)
                ->where('code', $account->price_code);
        })->get();

        $paf_data = [
            'account' => $account,
            'branches' => $this->branches,
            'products' => $this->products,
        ];

        Session::put('paf_data', $paf_data);
    }

    public function removeLine($key) {
        unset($this->details[$key]);

        $paf_data = Session::get('paf_data');
        $paf_data['details'] = $this->details;
        Session::put('paf_data', $paf_data);
    }
    
    public function mount($paf) {
        $this->paf = $paf;

        $this->accounts = $this->paf->user->accounts;
        $this->support_types = PafSupportType::all();
        $this->expense_types = PafExpenseType::all();

        $header = [
            'pre_plan_number' => $this->paf->pre_plan_number,
            'account' => $this->paf->account ?? NULL,
            'support_type' => $this->paf->support_type ?? NULL,
            'expense_type' => $this->paf->expense_type ?? NULL,
            'title' => $this->paf->title,
            'start_date' => $this->paf->start_date,
            'end_date' => $this->paf->end_date,
            'activity' => $this->paf->activity
        ];

        foreach($this->paf->paf_details as $detail) {
            $this->details[] = [
                'type' => '',
                'quantity' => $detail->quantity,
                'srp' => $detail->srp,
                'percentage' => $detail->percentage,
                'type' => $detail->type,
                'amount' => $detail->amount,
                'expense' => $detail->expense,
                'product_id' => $detail->product ?? NULl,
                'product' => ($detail->product->stock_code ?? '') . ' '.($detail->product->description ?? '').' '.($detail->product->size ?? ''),
                'branch' => $detail->branch,
            ];
        }
        
        $this->account_id = $this->paf->account_id;
        $this->support_type_id = $this->paf->paf_support_type_id ?? NULL;
        $this->expense_type_id = $this->paf->paf_expense_type_id ?? NULL;
        $this->activity_id = $this->paf->activity_id ?? NULL;
        $this->title = $this->paf->title;
        $this->program_start = $this->paf->start_date;
        $this->program_end = $this->paf->end_date;

        $this->updatedAccountId();
    }

    public function render()
    {
        return view('livewire.paf.edit');
    }
}
