<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

use App\Http\Traits\GlobalTrait;

class SystemLogController extends Controller
{
    use GlobalTrait;

    public $settings;

    public function __construct() {
        $this->settings = $this->getSettings();
    }

    public function index(Request $request) {
        $search = trim($request->get('search'));
        $activities = Activity::orderBy('created_at', 'DESC')
        ->paginate($this->settings->data_per_page)->onEachSide(1);

        return view('system-logs.index')->with([
            'activities' => $activities,
            'search' => $search
        ]);
    }
}
