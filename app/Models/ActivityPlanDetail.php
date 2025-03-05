<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ActivityPlanDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_id',
        'user_id',
        'branch_id',
        'day',
        'date',
        'exact_location',
        'activity',
        'work_with',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function activity_plan() {
        return $this->belongsTo('App\Models\ActivityPlan');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function trip() {
        return $this->hasOne('App\Models\ActivityPlanDetailTrip');
    }
}
