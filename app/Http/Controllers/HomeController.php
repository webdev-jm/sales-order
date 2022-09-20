<?php

namespace App\Http\Controllers;

use AccountLoginModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
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
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();
        if(empty($logged_account)) {
            Session::forget('logged_account');
        } else {
            // check
            $check = auth()->user()->accounts()->where('id', $logged_account->account_id)->first();
            if(empty($check)) {
                Session::forget('logged_account');
                $logged_account->update([
                    'time_out' => now()
                ]);
            } else {
                Session::put('logged_account', $logged_account);
            }
        }

        $logged_account = auth()->user()->logged_account();
        return view('home')->with([
            'logged_account' => $logged_account
        ]);
    }
}
