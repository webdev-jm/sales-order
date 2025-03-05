<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BranchLogin extends Model
{
    use HasFactory;

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
        'operation_process_id',
        'action_points',
        'longitude',
        'latitude',
        'accuracy',
        'time_in',
        'time_out',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function operation_process() {
        return $this->belongsTo('App\Models\OperationProcess');
    }

    public function login_activities() {
        return $this->hasMany('App\Models\BranchLoginActivity');
    }

    public function channel_operations() {
        return $this->hasMany('App\Models\ChannelOperation');
    }

    public function userBranchSchedules() {
        return $this->hasMany('App\Models\UserBranchSchedule', 'user_id', 'user_id');
    }
}
