<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

use App\Models\PafSupportType;
use App\Models\PafExpenseType;
use App\Models\Paf;
use App\Models\PafDetail;

use Carbon\Carbon;

class Create extends Component
{
    public $accounts = [];
    public $support_types = [];
    public $expense_types = [];

    public $days = 30;

    public $account_id, $support_type_id, $expense_type_id;
    public $title, $program_start, $program_end;

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

    public function mount() {
        $this->accounts = auth()->user()->accounts;
        $this->support_types = PafSupportType::all();
        $this->expense_types = PafExpenseType::all();
    }

    public function render()
    {
        return view('livewire.paf.create');
    }
}
