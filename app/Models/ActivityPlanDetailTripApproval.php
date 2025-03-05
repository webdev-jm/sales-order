<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ActivityPlanDetailTripApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'activity_plan_detail_trip_id',
        'status',
        'remarks',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function trip() {
        return $this->belongsTo('App\Models\ActivityPlanDetailTrip');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
