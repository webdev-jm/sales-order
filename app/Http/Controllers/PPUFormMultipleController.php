<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PPUFormMultipleController extends Controller
{
    public function index() {
        $logged_account = Session::get('logged_account');
        if(empty($logged_account)) {
            return redirect()->route('home')->with([
                'message_error' => 'please sign in to account before creating ppu'
            ]);
        }

        return view('ppu-forms.multiple-uploads.index')->with([
            'logged_account' => $logged_account
        ]);
    }
}
