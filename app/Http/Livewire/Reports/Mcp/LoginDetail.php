<?php

namespace App\Http\Livewire\Reports\Mcp;

use Livewire\Component;

use App\Models\BranchLogin;

class LoginDetail extends Component
{
    public $branch_login, $branch_activities;
    
    protected $listeners = [
        'showDetail' => 'getLogin'
    ];

    public function getLogin($login_id) {
        $this->branch_login = BranchLogin::findOrFail($login_id);
        $this->branch_activities = $this->branch_login->login_activities()->whereNotNull('activity_id')->get();
    }

    public function render()
    {
        return view('livewire.reports.mcp.login-detail');
    }
}
