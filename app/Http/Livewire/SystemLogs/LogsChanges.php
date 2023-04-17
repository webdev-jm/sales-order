<?php

namespace App\Http\Livewire\SystemLogs;

use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class LogsChanges extends Component
{
    public $activity, $changes;

    protected $listeners = [
        'showChanges' => 'getChanges'
    ];

    public function getChanges($log_id) {
        $activity = Activity::findOrFail($log_id);
        $old = $activity->properties['old'];
        $changes = $activity->properties['changes'];

        $details = [];
        foreach($changes as $key => $update) {
            $details[$key]['old'] = $old[$key];
            $details[$key]['update'] = $update;
        }

        $this->activity = $activity;
        $this->changes = $details;
    }
    
    public function render()
    {
        return view('livewire.system-logs.logs-changes');
    }
}
