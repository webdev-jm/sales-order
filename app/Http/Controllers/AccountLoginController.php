<?php

namespace App\Http\Controllers;

use App\Models\AccountLogin;
use App\Models\Account;
use App\Http\Requests\StoreAccountLoginRequest;
use App\Http\Requests\UpdateAccountLoginRequest;

class AccountLoginController extends Controller
{
    public function login($id) {
        $account = Account::findOrFail($id);
    }
}
