<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
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

        return view('livewire.accounts.account-login')->with([
            'accounts' => $accounts
        ]);
    }
}
