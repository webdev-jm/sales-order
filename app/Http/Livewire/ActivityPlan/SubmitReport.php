<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\User;
use App\Models\OrganizationStructure;
use App\Models\ActivityPlan;

use Livewire\WithPagination;

class SubmitReport extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $year, $month, $subordinate_ids;

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }

        if(empty($this->month)) {
            $this->month = date('m');
        }

        $this->subordinate_ids = $this->getSubordinates(auth()->user()->id);
    }

    public function getSubordinates($user_id) {
        $user = User::findOrFail($user_id);
        $organizations = $user->organizations;
        $subordinate_ids = [];
        foreach($organizations as $organization) {
            $subordinates = OrganizationStructure::where('reports_to_id', $organization->id)
            ->get();
            foreach($subordinates as $subordinate) {
                if(!empty($subordinate->user_id)) {
                    $subordinate_ids[] = $subordinate->user_id;
                }
                // get second level subordinates
                $subordinates2 = OrganizationStructure::where('reports_to_id', $subordinate->id)
                ->get();
                foreach($subordinates2 as $subordinate2) {
                    if(!empty($subordinate2->user_id)) {
                        $subordinate_ids[] = $subordinate2->user_id;
                    }
                    // get third level subordinates
                    $subordinates3 = OrganizationStructure::where('reports_to_id', $subordinate2->id)
                    ->get();
                    foreach($subordinates3 as $subordinate3) {
                        if(!empty($subordinate3->user_id)) {
                            $subordinate_ids[] = $subordinate3->user_id;
                        }
                        // get fourth level subordinates
                        $subordinates4 = OrganizationStructure::where('reports_to_id', $subordinate3->id)
                        ->get();
                        foreach($subordinates4 as $subordinate4) {
                            if(!empty($subordinate4->user_id)) {
                                $subordinate_ids[] = $subordinate4->user_id;
                            }
                        }
                    }
                }
            }
        }

        // return and remove duplicates
        return array_unique($subordinate_ids);
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

        $users = User::orderBy('firstname', 'ASC')
        ->whereIn('id', $this->subordinate_ids)
        ->paginate(10, ['*'], 'report-page')->onEachSide(1);

        // check submission
        $submission_arr = [];
        foreach($users as $user) {
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
            'users' => $users,
            'submission_arr' => $submission_arr
        ]);
    }
}
