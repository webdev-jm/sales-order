<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBranchSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

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
