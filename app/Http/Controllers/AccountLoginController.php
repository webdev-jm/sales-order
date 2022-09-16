<?php

namespace App\Http\Controllers;

use App\Models\AccountLogin;
use App\Models\Account;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

class AccountLoginController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request) {
        $search = trim($request->get('search'));
        $accounts = Account::LoginAccountSearch($search, $this->setting->data_per_page);
        return view('account-logins.index')->with([
            'accounts' => $accounts,
            'search' => $search
        ]);
    }

    public function show(Request $request, $id) {
        $search = trim($request->get('search'));
        $account = Account::findOrFail($id);

        if($search != '') {
            $account_logins = AccountLogin::orderBy('time_in', 'DESC')
            ->where('account_id', $id)
            ->whereHas('user', function($query) use ($search) {
                $query->where('firstname', 'like', '%'.$search.'%')
                ->orWhere('lastname', 'like', '%'.$search.'%');
            })
            ->paginate($this->setting->data_per_page)->onEachSide(1)
            ->appends(request()->query());
        } else {
            $account_logins = AccountLogin::orderBy('time_in', 'DESC')
            ->where('account_id', $id)
            ->paginate($this->setting->data_per_page)->onEachSide(1)
            ->appends(request()->query());
        }

        return view('account-logins.show')->with([
            'account_logins' => $account_logins,
            'account' => $account,
            'search' => $search
        ]);
    }
}
