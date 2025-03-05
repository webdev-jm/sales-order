<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ActivityPlanDetailTripDestination extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_detail_trip_id',
        'activity_plan_detail_id',
        'user_id',
        'from',
        'to',
        'departure',
        'return',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function trip() {
        return $this->belongsTo('App\Models\ActivityPlanDetailTrip', 'activity_plan_detail_trip_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
