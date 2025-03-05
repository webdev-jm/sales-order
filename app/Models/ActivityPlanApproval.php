<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ActivityPlanApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'activity_plan_id',
        'status',
        'remarks'
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
}
