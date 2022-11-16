<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReportActionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_activity_report_id',
        'action_plan',
        'time_table',
        'person_responsible',
    ];

    public function weekly_activity_report() {
        return $this->belongsTo('App\Models\WeeklyActivityReport');
    }
}
