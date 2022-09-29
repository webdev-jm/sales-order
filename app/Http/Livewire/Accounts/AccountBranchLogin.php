<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use App\Models\Branch;

use Livewire\WithPagination;

class AccountBranchLogin extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    
    public $account, $branch_accuracy, $branch_longitude, $branch_latitude;

    protected $listeners = ['setAccount' => 'setAccount'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setAccount($account_id) {
        $this->account = Account::findOrFail($account_id);
    }

    public function render()
    {
        $branches = [];
        if(!empty($this->account)) {
            $branches = Branch::orderBy('branch_code', 'ASC')
            ->where('account_id', $this->account->id)
            ->paginate(1, ['*'], 'branchPage')
            ->onEachSide(1)->appends(request()->query());
        }

        return view('livewire.accounts.account-branch-login')->with([
            'branches' => $branches
        ]);
    }
}
