<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

use App\Models\PafSupportType;
use App\Models\PafExpenseType;
use App\Models\Paf;
use App\Models\PafDetail;
use App\Models\PafActivity;
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
    public $title, $program_start, $program_end;
    public $details = [];

    protected $listeners = [
        'setDetail' => 'setDetail'
    ];

    public function save() {
        $this->validate([
            'account_id' => [
                'required',
            ],
            'support_type_id' => [
                'required',
            ],
            'expense_type_id' => [
                'required'
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

        $type = checkPafType($this->program_start);

        $paf = new Paf([
            'account_id' => $this->account_id,
            'user_id' => auth()->user()->id,
            'paf_expense_type_id' => $this->expense_type_id,
            'paf_support_type_id' => $this->support_type_id,
            'paf_number' => $this->generatePafNumber($type),
            'title' => $this->title,
            'start_date' => $this->program_start,
            'end_date' => $this->program_end,
            'status' => 'draft',
            'concept' => ''
        ]);
        $paf->save();

        if(!empty($this->details)) {
            foreach($this->details as $val) {
                $paf_detail = new PafDetail([
                    'paf_id' => $paf->id,
                    'paf_activity_id' => $this->activity_id,
                    'product_id' => $val['product_id'],
                    'branch_id' => $val['branch_id'],
                    'amount' => $val['amount'],
                    'expense' => $val['expense'],
                    'quantity' => $val['quantity'],
                    'srp' => $val['srp'],
                    'percentage' => $val['percentage'],
                    'status' => NULL,
                ]);
                $paf_detail->save();
            }
        }
    }

    private function generatePafNumber($type) {
         // Initial code format, adjust this as needed
         $prefix = date('Y').'-'.$type.'-';
         $initialNumber = 1;
         $code = $prefix . str_pad($initialNumber, 5, '0', STR_PAD_LEFT);
 
         do {
             // Get the last code from the Equipment model
             $last_paf = Paf::orderBy('paf_number', 'desc')->first();
 
             if ($last_paf) {
                 // Extract the numeric part and increment it
                 $lastNumber = (int) str_replace($prefix, '', $last_paf->paf_number);
                 $newNumber = $lastNumber + 1;
                 $code = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
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
                $product = $this->products->where('id', $detail['product_id'])->first();
                $paf_data['details']['product'] = $product;
            }
            $branch;
            if(!empty($detail['branch_id'])) {
                $branch = $this->branches->where('id', $detail['branch_id'])->first();
                $paf_data['details']['branch'] = $product;
            }
        }

        $this->details = $paf_data['details'];
    }

    public function mount() {
        $this->accounts = auth()->user()->accounts;
        $this->support_types = PafSupportType::all();
        $this->expense_types = PafExpenseType::all();

        $paf_data = Session::get('paf_data');
        $this->details = $paf_data['details'] ?? [];
    }

    public function render()
    {
        return view('livewire.paf.create');
    }
}
