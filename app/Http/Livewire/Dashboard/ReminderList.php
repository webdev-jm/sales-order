<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Reminders;

class ReminderList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $reminders = Reminders::orderByDesc('date')
            ->whereNull('status')
            ->where('user_ids', 'like', ','.auth()->user()->id.',')
            ->paginate(10, ['*'], 'reminder-page')->onEachSide(1);

        return view('livewire.dashboard.reminder-list')->with([
            'reminders' => $reminders
        ]);
    }
}
