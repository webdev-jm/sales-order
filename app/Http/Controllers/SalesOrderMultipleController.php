<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SalesOrderMultipleController extends Controller
{
    public function index() {
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating sales order'
            ]);
        }

        return view('sales-orders.multiple-uploads.index')->with([
            'logged_account' => $logged_account
        ]);
    }
}
