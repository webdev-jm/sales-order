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
        $hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $users = User::orderBy('last_activity', 'DESC')
        ->whereNotNull('last_activity')
        ->where('last_activity', '>=', $hour_ago)
        ->paginate(10, ['*'], 'online-page')->onEachSide(1);

        return view('livewire.online-users')->with([
            'users' => $users,
        ]);
    }
}
