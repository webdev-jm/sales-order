<?php

namespace App\Http\Livewire\Coe;

use Livewire\Component;

use Illuminate\Support\Facades\DB;

class Filter extends Component
{
    public $user_data;
    public $account_data;

    public function selectAccount($account_id) {
        if(!empty($this->account_data) && in_array($account_id, $this->account_data)) {
            unset($this->account_data[array_search($account_id, $this->account_data)]);
        } else {
            $this->account_data[] = $account_id;
        }

        $this->emit('setAccount', $this->account_data);
    }

    public function selectUser($user_id) {
        if(!empty($this->user_data) && in_array($user_id, $this->user_data)) {
            unset($this->user_data[array_search($user_id, $this->user_data)]);
        } else {
            $this->user_data[] = $user_id;
        }

        $this->emit('setUser', $this->user_data);
    }
    
    public function clearUserFilter() {
        $this->reset('user_data');
        $this->emit('setUser', $this->user_data);
    }

    public function clearAccountFilter() {
        $this->reset('account_data');
        $this->emit('setAccount', $this->account_data);
    }

    public function render()
    {
        $users = DB::table('users as u')
            ->select(
                'u.id',
                DB::raw('CONCAT(u.firstname, " ", u.lastname) as name')
            )
            ->join('branch_logins as bl', 'bl.user_id', '=', 'u.id')
            ->join('channel_operations as co', 'co.branch_login_id', '=', 'bl.id')
            ->join('branches as b', 'b.id', '=', 'bl.branch_id')
            ->groupBy('id', 'name')
            ->when(!empty($this->account_data), function($query) {
                $query->whereIn('b.account_id', $this->account_data);
            })
            ->get();

        $accounts = DB::table('accounts as a')
            ->select(
                'a.id',
                'a.short_name'
            )
            ->join('branches as b', 'b.account_id', '=', 'a.id')
            ->join('branch_logins as bl', 'bl.branch_id', '=', 'b.id')
            ->join('channel_operations as co', 'co.branch_login_id', '=', 'bl.id')
            ->groupBy('id', 'short_name')
            ->when(!empty($this->user_data), function($query) {
                $query->whereIn('bl.user_id', $this->user_data);
            })
            ->get();

        return view('livewire.coe.filter')->with([
            'users' => $users,
            'accounts' => $accounts
        ]);
    }
}
