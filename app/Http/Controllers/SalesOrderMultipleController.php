<?php

namespace App\Http\Controllers;

use App\Services\AccountLoginResolver;
use App\Services\SalesOrderRestriction;

class SalesOrderMultipleController extends Controller
{
    public function __construct(
        protected AccountLoginResolver $accountLoginResolver,
        protected SalesOrderRestriction $salesOrderRestriction
    ) {
    }

    public function index() {
        $logged_account = $this->accountLoginResolver->resolve();
        if(empty($logged_account)) {
            return redirect()->route('sales-order.index')->with([
                'message_error' => 'please select an active account before creating sales order'
            ]);
        }

        if($this->salesOrderRestriction->isRestricted($logged_account->account)) {
            return redirect()->route('sales-order.index')->with([
                'message_error' => $this->salesOrderRestriction->message($logged_account->account)
            ]);
        }

        return view('pages.sales-orders.multiple-uploads.index')->with([
            'logged_account' => $logged_account
        ]);
    }
}
