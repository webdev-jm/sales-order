<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class UserBranchSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'user_id',
        'branch_id',
        'activity_plan_detail_trip_id',
        'date',
        'status',
        'reschedule_date',
        'objective',
        'source'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function approvals() {
        return $this->hasMany('App\Models\UserBranchScheduleApproval');
    }

    public function trip() {
        return $this->belongsTo('App\Models\ActivityPlanDetailTrip', 'activity_plan_detail_trip_id', 'id');
    }

    public function BranchLogin() {
        return $this->hasMany('App\Models\BranchLogin', 'user_id', 'user_id')
            ->whereColumn('branch_logins.branch_id', 'user_branch_schedules.branch_id')
            ->whereColumn(DB::raw('DATE(branch_logins.time_in)'), 'user_branch_schedules.date');
    }
}
