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
        $logged_account = auth()->user()->logged_account();
        return view('home')->with([
            'logged_account' => $logged_account
        ]);
    }
}
