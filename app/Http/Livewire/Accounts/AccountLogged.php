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
    public $sign_out_enabled;

    protected $listeners = [
        'setSignout' => 'setSignout',
    ];

    public function setSignout() {
        $this->sign_out_enabled = 0;
        $check = ChannelOperation::where('branch_login_id', $this->logged_branch->id)
            ->first();
        if((!empty($check) && $check->status == 'finalized') || $this->logged_branch->branch->account_id != 241) {
            $this->sign_out_enabled = 1;
        }
    }

    public function loggedForm() {
        $this->dispatchBrowserEvent('openLoggedModal'.$this->logged->id);
    }

    public function loggedBranchForm() {
        $this->emit('reloadActivities');
        $this->dispatchBrowserEvent('openLoggedModal'.$this->logged_branch->id);
    }

    public function mount() {
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();
        if(empty($logged_account)) {
            Session::forget('logged_account');
        } else {
            // check
            $check = Account::where(function($query) {
                $query->whereHas('users', function($qry) {
                    $qry->where('user_id', auth()->user()->id);
                })
                ->orWhereHas('sales_people', function($qry) {
                    $qry->where('user_id', auth()->user()->id);
                });
            })
            ->where('id', $logged_account->account_id)->first();
            
            if(empty($check)) {
                Session::forget('logged_account');
                $logged_account->update([
                    'time_out' => now()
                ]);
            } else {
                Session::put('logged_account', $logged_account);
            }

        }
        
        $this->logged = Session::get('logged_account');

        $logged_branch = BranchLogin::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        if(empty($logged_branch)) {
            Session::forget('logged_branch');
        } else {
            Session::put('logged_branch', $logged_branch);
        }

        $this->logged_branch = Session::get('logged_branch');
        
        if(!empty($this->logged_branch) && $this->logged_branch->user->coe && $this->logged_branch->branch->account_id != 241) {
            $this->setSignout();
        } else {
            $this->sign_out_enabled = 1;
        }
    }

    public function render()
    {
        return view('livewire.accounts.account-logged');
    }
}
