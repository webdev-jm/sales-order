<?php

namespace App\Http\Livewire\AccountLogins;

use Livewire\Component;
use App\Models\AccountLogin;
use App\Models\SalesOrder;
use Livewire\WithPagination;

class AccountLoginActivities extends Component
{
    use WithPagination;

    public $login_id, $account_login;

    protected $listeners = [
        'showActivities' => 'getActivities'
    ];

    protected $paginationTheme = 'bootstrap';

    public function getActivities($id) {
        $this->login_id = $id;
        $this->account_login = AccountLogin::findOrFail($id);
    }

    public function render()
    {
        $sales_orders = SalesOrder::orderBy('order_date')
        ->where('account_login_id', $this->login_id)
        ->paginate(10)->onEachSide(1)->appends(request()->query());

        return view('livewire.account-logins.account-login-activities')->with([
            'sales_orders' => $sales_orders
        ]);
    }
}
