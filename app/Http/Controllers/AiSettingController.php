<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AiSettingController extends Controller
{
    public function index(): View
    {
        return view('pages.ai-settings.index');
    }
}
