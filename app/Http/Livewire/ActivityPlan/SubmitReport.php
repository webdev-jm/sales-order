<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\User;
use App\Models\OrganizationStructure;
use App\Models\ActivityPlan;

use Livewire\WithPagination;

class SubmitReport extends Component
{
    // use WithPagination;
    // protected $paginationTheme = 'bootstrap';

    public $year, $month, $subordinate_ids;

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }

        if(empty($this->month)) {
            $this->month = date('m');
        }

        $this->subordinate_ids = [];
        $subordinate_ids = auth()->user()->getSubordinateIds();
        foreach($subordinate_ids as $level => $ids) {
            foreach($ids as $id) {
                $this->subordinate_ids[] = $id;
            }
        }

        $this->subordinate_ids = array_unique($this->subordinate_ids);
    }

    public function render()
    {
        $months_arr = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];

        $submit_users = User::orderBy('firstname', 'ASC')
            ->whereIn('id', $this->subordinate_ids)->get();

        // check submission
        $submission_arr = [];
        foreach($submit_users as $user) {
            $check = ActivityPlan::where('year', $this->year)
            ->where('month', $this->month)
            ->where('user_id', $user->id)
            ->where('status', '<>', 'draft')
            ->first();
            if(!empty($check)) { // submitted
                $submission_arr[$user->id] = [
                    'status' => 'submitted',
                ];
            } else {
                $submission_arr[$user->id] = [
                    'status' => 'not submitted',
                ];
            }
        }

        return view('livewire.activity-plan.submit-report')->with([
            'months' => $months_arr,
            'submit_users' => $submit_users,
            'submission_arr' => $submission_arr
        ]);
    }
}
