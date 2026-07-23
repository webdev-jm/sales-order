<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\BranchLogin;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
     * Accounts the user is allowed to set as the active account.
     *
     * Sales orders and PPU forms may be raised for any account assigned to the
     * user, regardless of the branch the user happens to be signed in to.
     */
    public function assignedAccounts(User $user): Builder
    {
        return Account::query()->where(function (Builder $query) use ($user) {
            $query->whereHas('users', function (Builder $qry) use ($user) {
                $qry->where('user_id', $user->id);
            })->orWhereHas('sales_people', function (Builder $qry) use ($user) {
                $qry->where('user_id', $user->id);
            });
        });
    }

    /**
     * Determine whether the account is assigned to the user.
     */
    public function isAssignedTo(User $user, int $account_id): bool
    {
        return $this->assignedAccounts($user)->whereKey($account_id)->exists();
    }

    /**
     * Set the account the user tags sales orders and PPU forms with.
     *
     * Any account login still open is closed first so a user only ever has one
     * active account, and the in-progress order data of the previous account is
     * discarded so it cannot leak into the new one.
     *
     * @param  array{longitude?: mixed, latitude?: mixed, accuracy?: mixed}  $coordinates
     */
    public function switchTo(User $user, Account $account, array $coordinates = []): AccountLogin
    {
        AccountLogin::where('user_id', $user->id)
            ->whereNull('time_out')
            ->update(['time_out' => now()]);

        $account_login = new AccountLogin([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'longitude'  => $coordinates['longitude'] ?? 0,
            'latitude'   => $coordinates['latitude'] ?? 0,
            'accuracy'   => $coordinates['accuracy'] ?? 'not available',
            'time_in'    => now(),
        ]);
        $account_login->save();

        activity('login')
            ->performedOn($account_login)
            ->log(':causer.firstname :causer.lastname has set account ' . ($account->short_name ?? $account->account_name) . ' as the active account');

        Session::put('logged_account', $account_login);
        Session::forget('order_data');
        Session::forget('ppu_item');

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
