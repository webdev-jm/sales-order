<?php

namespace App\Http\Controllers;

use App\Services\SalesOrderRestriction;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected SalesOrderRestriction $salesOrderRestriction)
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $logged_branch = auth()->user()->logged_branch();

        // A branch login takes precedence: the account login it derives is only
        // used to tag sales orders, not to switch the home screen.
        $logged_account = empty($logged_branch) ? auth()->user()->logged_account() : null;

        $account = $logged_account->account ?? $logged_branch->branch->account ?? null;

        return view('home')->with([
            'logged_account' => $logged_account,
            'logged_branch' => $logged_branch,
            'restricted' => $this->salesOrderRestriction->isRestricted($account),
            'restricted_message' => $this->salesOrderRestriction->message($account)
        ]);
    }
}
