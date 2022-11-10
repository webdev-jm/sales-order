<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlanDetail;

class ScheduleDetail extends Component
{
    public $detail;

    protected $listeners = [
        'setDetail' => 'setDetail'
    ];

    public function setDetail($detail_id) {
        $this->detail = ActivityPlanDetail::find($detail_id);
    }

    public function render()
    {
        return view('livewire.activity-plan.schedule-detail');
    }
}
