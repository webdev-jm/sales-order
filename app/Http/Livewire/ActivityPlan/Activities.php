<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Activity;

use Illuminate\Support\Facades\Session;

class Activities extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $year, $month, $date, $key, $search;
    public $activity_data;

    protected $listeners = [
        'setActivities' => 'setData'
    ];

    public function updatedSearch() {
        $this->resetPage('activity-page');
    }

    public function clear() {
        $this->reset('activity_data');
        $this->resetPage('activity-page');
    }

    public function save() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data)) {
            $activity_ids = [];
            foreach($this->activity_data as $activity_id) {
                $activity_ids[] = $activity_id;
            }
            
            $activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['activity_ids'] = $activity_ids;

            Session::put('activity_plan_data', $activity_plan_data);
        } else {

        }

        $this->reset('activity_data');
        $this->resetPage('activity-page');
    }

    public function setData($year ,$month, $date, $key) {
        $this->year = $year;
        $this->month = $month;
        $this->date = $date;
        $this->key = $key;

        $this->reset('activity_data');
        $this->resetPage('activity-page');

        // set data
        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['activity_ids'])) {
            $activity_ids = $activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['activity_ids'];
            foreach($activity_ids as $activity_id) {
                $this->activity_data[$activity_id] = $activity_id;
            }
        }
    }

    public function render()
    {
        
        if(!empty($this->search)) {
            $activities = Activity::orderBy('operation_process_id', 'ASC')
            ->orderBy('number', 'ASC')
            ->where('description', 'like', '%'.$this->search.'%')
            ->orWhere('remarks', 'like', '%'.$this->search.'%')
            ->orWhereHas('operation_process', function($query) {
                $query->where('operation_process', 'like', '%'.$this->search.'%');
            })
            ->paginate(5, ['*'], 'activity-page');
        } else {
            $activities = Activity::orderBy('operation_process_id', 'ASC')
            ->orderBy('number', 'ASC')
            ->paginate(5, ['*'], 'activity-page');
        }

        return view('livewire.activity-plan.activities')->with([
            'activities' => $activities
        ]);
    }
}