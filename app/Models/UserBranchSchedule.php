<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBranchSchedule extends Model
{
    use HasFactory;

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
        return $this->belongsTo('App\Models\User');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function approvals() {
        return $this->hasMany('App\Models\UserBranchScheduleApproval');
    }

    public function trip() {
        return $this->hasOne('App\Models\ActivityPlanDetailTrip');
    }

    public function BranchLogin() {
        return $this->hasMany('App\Models\BranchLogin', 'user_id', 'user_id')
            ->whereColumn('branch_logins.branch_id', 'user_branch_schedules.branch_id')
            ->whereColumn(DB::raw('DATE(branch_logins.time_in)'), 'user_branch_schedules.date');
    }
}
