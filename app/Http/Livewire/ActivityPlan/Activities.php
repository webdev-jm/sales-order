<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use Illuminate\Support\Facades\Session;

class Activities extends Component
{
    public $month, $date, $key;

    protected $listeners = [
        'setActivities' => 'setData'
    ];

    public function setData($month, $date, $key) {
        $this->month = $month;
        $this->date = $date;
        $this->key = $key;
    }

    public function mount() {
        $activity_plan_data = Session::get('activity_plan_data');
    }

    public function render()
    {
        return view('livewire.activity-plan.activities');
    }
}