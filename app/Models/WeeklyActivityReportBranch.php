<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Session;

class WeeklyActivityReportBranch extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'weekly_activity_report_area_id',
        'branch_id',
        'user_branch_schedule_id',
        'branch_login_id',
        'status',
        'action_points',
    ];

    public function war_area() {
        return $this->belongsTo('App\Models\WeeklyActivityReportArea');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function schedule() {
        return $this->belongsTo('App\Models\UserBranchSchedule');
    }

    public function branch_login() {
        return $this->belongsTo('App\Models\BranchLogin');
    }

    public function attachments() {
        return $this->hasMany('App\Models\WeeklyActivityReportAttachment');
    }
}
