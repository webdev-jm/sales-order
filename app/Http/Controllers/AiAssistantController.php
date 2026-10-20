<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AiAssistantController extends Controller
{
    public function index(): View
    {
        return view('pages.ai-assistant.index');
    }
}
