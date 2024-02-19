<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPlanDetailTrip extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_detail_id',
        'user_id',
        'trip_number',
        'from',
        'to',
        'departure',
        'return',
        'amount',
        'trip_type',
        'transportation_type',
        'passenger',
        'purpose',
        'status',
        'source',
    ];

    public function schedule() {
        return $this->hasOne('App\Models\UserBranchSchedule');
    }

    public function activity_plan_detail() {
        return $this->belongsTo('App\Models\ActivityPlanDetail');
    }

    public function approvals() {
        return $this->hasMany('App\Models\ActivityPlanDetailTripApproval');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function attachments() {
        return $this->hasMany('App\Models\ActivityPlanDetailTripAttachment');
    }
}