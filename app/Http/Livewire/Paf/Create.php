<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

use App\Models\PafSupportType;
use App\Models\PafExpenseType;
use App\Models\Paf;
use App\Models\PafDetail;
use App\Models\PafActivity;
use App\Models\PafPrePlan;
use App\Models\PafApproval;
use App\Models\Product;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
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

    protected $listeners = [
        'setDetail' => 'setDetail',
        'setPrePlan' => 'setPrePlan'
    ];

    public function savePaf($status) {
        $this->validate([
            'account_id' => [
                'required',
            ],
            'support_type_id' => [
                'required',
            ],
            'expense_type_id' => [
                'required',
            ],
            'title' => [
                'required',
            ],
            'program_start' => [
                'required',
            ],
            'program_end' => [
                'required',
            ],
        ]);

        $type = $this->checkPafType($this->program_start);

        $paf = new Paf([
            'account_id' => $this->account_id,
            'user_id' => auth()->user()->id,
            'paf_expense_type_id' => $this->expense_type_id,
            'paf_support_type_id' => $this->support_type_id,
            'paf_activity_id' => $this->activity_id ?? NULL,
            'paf_number' => $this->generatePafNumber($type),
            'title' => $this->title,
            'start_date' => $this->program_start,
            'end_date' => $this->program_end,
            'status' => $status,
            'concept' => ''
        ]);
        $paf->save();

        if(!empty($this->details)) {
            foreach($this->details as $val) {
                if(!empty($val['product_id']) && !empty($val['quantity'])) {
                    $paf_detail = new PafDetail([
                        'paf_id' => $paf->id,
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
            $pre_plan = PafPrePlan::where('pre_plan_number', $this->pre_plan_number)
                ->first();

            if(!empty($pre_plan)) {
                $pre_plan->update([
                    'paf_id' => $paf->id
                ]);
            }
        }

        if($status == 'submitted') {
            // record approvals
            $approval = new PafApproval([
                'paf_id' => $paf->id,
                'user_id' => auth()->user()->id,
                'status' => $status,
                'remarks' => NULL
            ]);
            $approval->save();
        }

        session()->flash('message_success', 'PAF '.$paf->paf_number.' has been created.');
        
        return redirect()->route('paf.index');
    }

    private function generatePafNumber($type) {
         // Initial code format, adjust this as needed
         $prefix = date('Y').'-'.$type.'-';
         $initialNumber = 1;
         $code = $prefix . str_pad($initialNumber, 5, '0', STR_PAD_LEFT);
 
         do {
             // Get the last code from the Equipment model
             $last_paf = Paf::orderBy('created_at', 'desc')->first();
 
             if ($last_paf) {
                // Extract the numeric part and increment it
                $number_arr = explode('-', $last_paf->paf_number);
                $lastNumber = (int)end($number_arr);
                $newNumber = $lastNumber + 1;
                $code = $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
             }
 
             // Check if the new code already exists
             $exists = Paf::where('paf_number', $code)->exists();
         } while($exists);
 
         return $code;
    }

    private function checkPafType($start_date) {
        $type = 'D';
        // check type
        if(date('Y-m-d') > $start_date) { // late
            $type = 'P';
            return $type;
        }

        $curr_date = Carbon::parse(date('Y-m-d'));
        $start_date = Carbon::parse($start_date);
        $days_diff = $curr_date->diffInDays($start_date);
        if($days_diff <= $this->days) { // short dated
            $type = 'S';
        } else {
            $type = 'D';
        }

        return $type;
    }

    public function updatedAccountId() {
        $account = $this->accounts->where('id', $this->account_id)->first();
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

    public function setDetail() {
        $paf_data = Session::get('paf_data');

        foreach($paf_data['details'] as $detail) {
            $product;
            if(!empty($detail['product_id'])) {
                $product = collect($this->products)->where('id', $detail['product_id'])->first();
                $paf_data['details']['product'] = $product;
            }
            $branch;
            if(!empty($detail['branch_id'])) {
                $branch = collect($this->products)->where('id', $detail['branch_id'])->first();
                $paf_data['details']['branch'] = $product;
            }
        }

        $this->details = $paf_data['details'];
    }

    public function setPrePlan() {
        $paf_data = Session::get('paf_data');
        $this->details = $paf_data['details'] ?? [];
        if(!empty($paf_data['header'])) {
            $header = $paf_data['header'];
            $this->account_id = $header['account']['id'];
            $this->support_type_id = $header['support_type']['id'] ?? NULL;
            $this->expense_type_id = $header['expense_type']['id'] ?? NULL;
            $this->activity_id = $header['activity']['id'] ?? NULL;
            $this->title = $header['title'];
            $this->program_start = $header['start_date'];
            $this->program_end = $header['end_date'];
            $this->pre_plan_number = $header['pre_plan_number'];

            $account = $this->accounts->where('id', $this->account_id)->first();
            $this->activities = PafActivity::where('company_id', $account->company_id)->get();
        }

    }

    public function removeLine($key) {
        unset($this->details[$key]);

        $paf_data = Session::get('paf_data');
        $paf_data['details'] = $this->details;
        Session::put('paf_data', $paf_data);
    }

    public function mount() {
        $this->accounts = auth()->user()->accounts;
        $this->support_types = PafSupportType::all();
        $this->expense_types = PafExpenseType::all();

        $paf_data = Session::get('paf_data');
        $this->details = $paf_data['details'] ?? [];
        if(!empty($paf_data['header'])) {
            $header = $paf_data['header'];
            $this->account_id = $header['account']['id'];
            $this->support_type_id = $header['support_type']['id'] ?? NULL;
            $this->expense_type_id = $header['expense_type']['id'] ?? NULL;
            $this->activity_id = $header['activity']['id'] ?? NULL;
            $this->title = $header['title'];
            $this->program_start = $header['start_date'];
            $this->program_end = $header['end_date'];

            $account = $this->accounts->where('id', $this->account_id)->first();
            $this->activities = PafActivity::where('company_id', $account->company_id)->get();
        }
    }

    public function render()
    {
        return view('livewire.paf.create');
    }
}
