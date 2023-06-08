<?php

namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\ActivityPlan;

class ActivityPlans extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public function render()
    {
        $activity_plans = ActivityPlan::orderBy('created_at', 'DESC')
            ->where('user_id', auth()->user()->id)
            ->paginate(10, ['*'], 'activity-plan-page');

        return view('livewire.profile.activity-plans')->with([
            'activity_plans' => $activity_plans
        ]);
    }
}
