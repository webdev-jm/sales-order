<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class OnlineUsers extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $users = User::orderBy('last_activity', 'DESC')
        ->whereNotNull('last_activity')
        ->paginate(10, ['*'], 'online-page')->onEachSide(1);

        return view('livewire.online-users')->with([
            'users' => $users
        ]);
    }
}
