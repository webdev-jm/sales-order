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
        if($search != '') {
            $activities = Activity::orderBy('created_at', 'DESC')
            ->where('log_name', 'like', '%'.$search.'%')
            ->orWhere('description', 'like', '%'.$search.'%')
            ->orWhereHas('causer', function($query) use ($search) {
                $query->where('firstname', 'like', '%'.$search.'%')
                ->orWhere('lastname', 'like', '%'.$search.'%');
            })
            ->paginate($this->settings->data_per_page)->onEachSide(1)
            ->appends(request()->query());
        } else {
            $activities = Activity::orderBy('created_at', 'DESC')
            ->paginate($this->settings->data_per_page)->onEachSide(1)
            ->appends(request()->query());
        }
        

        return view('system-logs.index')->with([
            'activities' => $activities,
            'search' => $search
        ]);
    }
}
