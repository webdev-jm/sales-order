<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use App\Models\UserBranchSchedule;
use App\Models\Deviation;

class ScheduleDetails extends Component
{
    public $schedule;
    public $deviation_data;

    protected $listeners = [
        'showScheduleDetail' => 'setData'
    ];

    public function setData($schedule_id) {
        $this->schedule = UserBranchSchedule::find($schedule_id);

        if($this->schedule->source == 'deviation') {
            // get deviation details
            $this->deviation_data = Deviation::where('user_id', $this->schedule->user_id)
                ->where('date', $this->schedule->date)
                ->first();
            
        }
    }

    public function render()
    {
        return view('livewire.reports.mcp.schedule-details');
    }
}
