<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class UserBranchScheduleApproval extends Model
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
        'user_branch_schedule_id',
        'user_id',
        'status',
        'remarks',
    ];

    public function user_branch_schedule() {
        return $this->belongsTo('App\Models\UserBranchSchedule');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
