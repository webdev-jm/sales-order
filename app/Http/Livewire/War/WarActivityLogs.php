<?php

namespace App\Http\Livewire\War;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\WeeklyActivityReport;
use Spatie\Activitylog\Models\Activity;

class WarActivityLogs extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $war;

    public function mount($war) {
        $this->war = $war;
    }

    public function render()
    {
        $activities = Activity::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('activity_log')
                    ->where('subject_type', WeeklyActivityReport::class)
                    ->where('subject_id', $this->war->id)
                    ->groupBy('causer_id', 'log_name');
            })
            ->orderByDesc('created_at')
            ->paginate(5, ['*'], 'activity-page');
        
        return view('livewire.war.war-activity-logs')->with([
            'activities' => $activities
        ]);
    }
}
