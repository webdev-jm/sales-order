<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\SalesOrder;

class AccountReport extends Component
{
    public $account, $data;

    protected $listeners = [
        'setAccount' => 'setAccount'
    ];

    public function setAccount($account_id) {
        $this->reset('data');

        $this->account = Account::findOrFail($account_id);
        $users = AccountLogin::select('user_id')->distinct()->where('account_id', $this->account->id)
        ->whereHas('sales_orders', function($query) {
            $query->where('status', '<>', 'draft');
        })->get();

        foreach($users as $user) {
            $count = SalesOrder::where('status', '<>', 'draft')
            ->whereHas('account_login', function($query) use ($user) {
                $query->where('user_id', $user->user_id)
                ->where('account_id', $this->account->id);
            })->count();

            $this->data[] = [
                'name' => $user->user->firstname.' '.$user->user->lastname,
                'count' => $count
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard.account-report');
    }
}
