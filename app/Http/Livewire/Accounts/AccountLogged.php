<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use AccountLoginModel;
use App\Models\Account;
use App\Models\BranchLogin;
use App\Models\ChannelOperation;

class AccountLogged extends Component
{
    public $logged, $logged_branch;
    public $sign_out_enabled = 1;

    protected $listeners = ['setSignout' => 'setSignout'];

    public function setSignout()
    {
        if($this->logged_branch) {

            if ($this->logged_branch && $this->logged_branch->user->coe && $this->logged_branch->branch->account_id !== 241) {
                $this->sign_out_enabled = 0;
                $check = ChannelOperation::where('branch_login_id', $this->logged_branch->id)->first();
    
                if((!empty($check) && $check->status === 'finalized') || $this->logged_branch->branch->account_id !== 241) {
                    $this->sign_out_enabled = 1;
                }
            } else {
                $this->sign_out_enabled = 1;
                if(empty($this->logged_branch->operation_process_id)) {
                    // check activity remarks
                    $activity = $this->logged_branch->login_activities()->first();
                    if(empty($activity->remarks)) {
                        $this->sign_out_enabled = 0;
                    }
                }

                if(empty($this->logged_branch->action_points)) {
                    $this->sign_out_enabled = 0;
                }
            }
        }
        
    }

    public function loggedForm()
    {
        $this->dispatchBrowserEvent('openLoggedModal' . $this->logged->id);
    }

    public function loggedBranchForm()
    {
        $this->emit('reloadActivities');
        $this->dispatchBrowserEvent('openLoggedModal' . $this->logged_branch->id);
    }

    public function mount()
    {
        $userId = auth()->user()->id;

        // Fetch logged account
        $this->logged = AccountLoginModel::where('user_id', $userId)
            ->whereNull('time_out')
            ->first();

        if ($this->logged) {
            $exists = Account::where('id', $this->logged->account_id)
                ->where(function ($query) use ($userId) {
                    $query->whereHas('users', function ($qry) use($userId) {
                            $qry->where('user_id', $userId);
                        })
                        ->orWhereHas('sales_people', function ($qry) use($userId) {
                            $qry->where('user_id', $userId);
                        });
                })->exists();

            if (!$exists) {
                $this->logged->update(['time_out' => now()]);
                $this->logged = null;
            }
        }

        Session::put('logged_account', $this->logged);

        // Fetch logged branch
        $this->logged_branch = BranchLogin::where('user_id', $userId)
            ->whereNull('time_out')
            ->first();

        Session::put('logged_branch', $this->logged_branch);

        $this->setSignout();
    }

    public function render()
    {
        return view('livewire.accounts.account-logged');
    }
}
