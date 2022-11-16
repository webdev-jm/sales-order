<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReportActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_activity_report_id',
        'activity',
        'no_of_days_weekly',
        'no_of_days_mtd',
        'no_of_days_ytd',
        'remarks',
        'percent_to_total_working_days',
    ];

    public function weekly_activity_report() {
        return $this->belongsTo('App\Models\WeeklyActivityReport');
    }
}
