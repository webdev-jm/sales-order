<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Livewire\WithPagination;

use Spatie\Activitylog\Models\Activity;

class ActivityLogs extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $search;

    public function updatedSearch() {
        $this->resetPage('activity-page');
    }

    public function mount() {
        $this->user = auth()->user();
    }
    
    public function render()
    {
        $activities = Activity::where('causer_id', $this->user->id)
            ->where('causer_type', get_class($this->user))
            ->when(!empty($this->search), function($query) {
                $query->where(function($qry) {
                    $qry->where('log_name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhereHas('causer', function($qry1) {
                            $qry1->where('firstname', 'like', '%'.$this->search.'%')
                            ->orWhere('lastname', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'activity-page')->onEachSide(1);
            
        return view('livewire.profile.activity-logs')->with([
            'activities' => $activities
        ]);
    }
}
