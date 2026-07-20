<?php

namespace App\Services;

use App\Models\Account;

class SalesOrderRestriction
{
    /**
     * Determine whether the account is barred from creating or uploading
     * sales orders, either directly or through one of its branches.
     */
    public function isRestricted(?Account $account): bool
    {
        if (empty($account) || empty($account->account_code)) {
            return false;
        }

        $restricted = array_map('strval', config('sales-order.restricted_accounts', []));

        return in_array((string) $account->account_code, $restricted, true);
    }

    /**
     * Message shown when a restricted account attempts to create a sales order.
     */
    public function message(?Account $account): string
    {
        return str_replace(
            ':account',
            $account->account_name ?? 'This account',
            config('sales-order.restricted_account_message')
        );
    }
}
