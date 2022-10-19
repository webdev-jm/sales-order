<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index() {
        $user = auth()->user();

        return view('profile')->with([
            'user' => $user
        ]);
    }
    
    public function notifications(Request $request) {

        $search = trim($request->get('search'));

        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10)->onEachSide(1);

        return view('notifications')->with([
            'search' => $search,
            'notifications' => $notifications
        ]);
    }
}