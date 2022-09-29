<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use App\Models\Branch;
use App\Models\BranchLogin;

use AccountLoginModel;

use Illuminate\Support\Facades\Session;

use Livewire\WithPagination;

class AccountBranchLogin extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    
    public $account, $branch_accuracy, $branch_longitude, $branch_latitude;
    public $branch, $accuracy, $longitude, $latitude;
    public $search;

    protected $listeners = ['setAccount' => 'setAccount'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function login() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        // check if logged in to account or branch
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        $logged_branch = BranchLogin::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        if(empty($logged_account) && empty($logged_branch)) {
            $branch_login = new BranchLogin([
                'user_id' => auth()->user()->id,
                'branch_id' => $this->branch->id,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'accuracy' => $this->accuracy,
                'time_in' => now(),
            ]);
            $branch_login->save();

            Session::put('logged_branch', $branch_login);
        }

        return redirect()->to('/home');
    }

    public function selectBranch($branch_id) {
        $this->branch = Branch::findOrFail($branch_id);
    }

    public function resetBranch() {
        $this->reset('branch');
    }

    public function loadLocation() {
        $this->dispatchBrowserEvent('reloadLocation');
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
            ->where(function($query) {
                $query->where('branch_code', 'like', '%'.$this->search.'%')
                ->orWhere('branch_name', 'like', '%'.$this->search.'%');
            })
            ->paginate(12, ['*'], 'branchPage')
            ->onEachSide(1)->appends(request()->query());
        }

        return view('livewire.accounts.account-branch-login')->with([
            'branches' => $branches
        ]);
    }
}
