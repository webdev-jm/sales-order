<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AiUsageController extends Controller
{
    public function index(): View
    {
        return view('pages.ai-usage.index');
    }
}
