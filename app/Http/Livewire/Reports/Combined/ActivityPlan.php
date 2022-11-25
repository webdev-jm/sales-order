<?php

namespace App\Http\Livewire\Reports\Combined;

use Livewire\Component;
use Livewire\WithPagination;

use ActivityPlanModel;

class ActivityPlan extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $month, $year, $user_id;

    protected $listeners = [
        'setActivityDate' => 'setDate'
    ];

    public function setDate($month, $year, $user_id) {
        $this->month = $month;
        $this->year = $year;
        $this->user_id = $user_id;
    }

    public function mount() {
        // set default date
        if(empty($this->year)) {
            $this->year = date('Y');
        }
        if(empty($this->month)) {
            $this->month = date('m');
        }
    }

    public function render()
    {
        $activity_plans = ActivityPlanModel::orderBy('id', 'DESC');
        if(!empty($this->month)) {
            $activity_plans->where('month', $this->month);
        }
        if(!empty($this->year)) {
            $activity_plans->where('year', $this->year);
        }
        if(!empty($this->user_id)) {
            $activity_plans->where('user_id', $this->user_id);
        }

        $activity_plans = $activity_plans->paginate(10, ['*'], 'activity-pages')
        ->onEachSide(1);

        $status_arr = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'rejected' => 'danger',
            'approved' => 'success'
        ];

        return view('livewire.reports.combined.activity-plan')->with([
            'activity_plans' => $activity_plans,
            'status_arr' => $status_arr
        ]);
    }
}