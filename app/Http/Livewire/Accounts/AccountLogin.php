<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use App\Models\SalesOrder;
use Livewire\WithPagination;

class AccountLogin extends Component
{
    use WithPagination;

    public $account;
    public $search;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function loginModal($account_id) {
        $this->account = Account::findOrFail($account_id);
        $this->emit('loginForm', $account_id);
        $this->dispatchBrowserEvent('openFormModal');
    }

    public function branchModal($account_id) {
        $this->emit('setAccount', $account_id);
        $this->dispatchBrowserEvent('openBranchModal');
    }

    public function render()
    {
        $accounts = Account::where(function($query) {
            $query->whereHas('users', function($qry) {
                $qry->where('user_id', auth()->user()->id);
            })
            ->orWhereHas('sales_people', function($qry) {
                $qry->where('user_id', auth()->user()->id);
            });
        })
        ->where(function($query) {
            $query->where('account_code', 'like', '%'.trim($this->search).'%')
            ->orWhere('account_name', 'like', '%'.trim($this->search).'%')
            ->orWhere('short_name', 'like', '%'.trim($this->search).'%');
        })
        ->paginate(12)->onEachSide(1)->appends(request()->query());

        $count_data = [];
        foreach($accounts as $account) {
            $count = SalesOrder::whereHas('account_login', function($query) use ($account) {
                $query->where('account_id', $account->id)
                ->where('user_id', auth()->user()->id);
            })->count();

            $count_data[$account->id] = $count;
        }

        return view('livewire.accounts.account-login')->with([
            'accounts' => $accounts,
            'count_data' => $count_data
        ]);
    }
}
