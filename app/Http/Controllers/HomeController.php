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
        if(empty(Session::get('logged_account'))) {
            $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
            ->whereNull('time_out')
            ->first();

            Session::put('logged_account', $logged_account);
        }

        $logged_account = auth()->user()->logged_account();
        return view('home')->with([
            'logged_account' => $logged_account
        ]);
    }
}
