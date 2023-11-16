<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use App\Models\UserBranchSchedule;
use App\Models\Deviation;
use App\Models\BranchLogin;

class ScheduleDetails extends Component
{
    public $schedule;
    public $deviation_data;
    public $type;
    public $branch_login;

    protected $listeners = [
        'showScheduleDetail' => 'setData'
    ];

    public function setData($schedule_id, $type) {
        $this->type = $type;
        if($type == 'schedule') {
            $this->schedule = UserBranchSchedule::find($schedule_id);
    
            if($this->schedule->source == 'deviation') {
                // get deviation details
                $this->deviation_data = Deviation::where('user_id', $this->schedule->user_id)
                    ->where('date', $this->schedule->date)
                    ->first();
            }
        } else {
            $this->branch_login = BranchLogin::find($schedule_id);
        }
    }

    public function render()
    {
        return view('livewire.reports.mcp.schedule-details');
    }
}
