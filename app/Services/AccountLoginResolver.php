<?php

namespace App\Services;

use App\Models\AccountLogin;
use App\Models\BranchLogin;
use Illuminate\Support\Facades\Session;

class AccountLoginResolver
{
    /**
     * Resolve the account login to tag sales orders with.
     *
     * Users signed in to a branch do not have an account login, so one is
     * derived from the account the branch belongs to, reusing the branch
     * login coordinates.
     */
    public function resolve(): ?AccountLogin
    {
        $user = auth()->user();

        if (empty($user)) {
            return null;
        }

        $account_login = AccountLogin::where('user_id', $user->id)
            ->whereNull('time_out')
            ->first();

        if (empty($account_login)) {
            $account_login = $this->createFromBranchLogin($user->id);
        }

        Session::put('logged_account', $account_login);

        return $account_login;
    }

    /**
     * Close the account login derived from a branch login, if any.
     */
    public function closeDerivedLogin(int $user_id): void
    {
        AccountLogin::where('user_id', $user_id)
            ->whereNull('time_out')
            ->update(['time_out' => now()]);

        Session::forget('logged_account');
    }

    /**
     * Create an account login out of the active branch login of the user.
     */
    private function createFromBranchLogin(int $user_id): ?AccountLogin
    {
        $branch_login = BranchLogin::with('branch')
            ->where('user_id', $user_id)
            ->whereNull('time_out')
            ->first();

        if (empty($branch_login) || empty($branch_login->branch->account_id)) {
            return null;
        }

        $account_login = new AccountLogin([
            'user_id'    => $user_id,
            'account_id' => $branch_login->branch->account_id,
            'longitude'  => $branch_login->longitude,
            'latitude'   => $branch_login->latitude,
            'accuracy'   => $branch_login->accuracy,
            'time_in'    => now(),
        ]);
        $account_login->save();

        activity('login')
            ->performedOn($account_login)
            ->log(':causer.firstname :causer.lastname has logged in to account ' . ($branch_login->branch->account->short_name ?? '') . ' from branch ' . $branch_login->branch->branch_name);

        return $account_login;
    }
}
