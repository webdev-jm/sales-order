<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ActivityPlanDetailTrip extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_detail_id',
        'user_id',
        'company_id',
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
        'invoice_number',
        'supplier',
    ];

    /**
     * Dynamically set the database connection based on the session.
    */
    public function getConnectionName() {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

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
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function attachments() {
        return $this->hasMany('App\Models\ActivityPlanDetailTripAttachment');
    }

    public function destinations() {
        return $this->hasMany('App\Models\ActivityPlanDetailTripDestination', 'activity_plan_detail_trip_id', 'id');
    }
}