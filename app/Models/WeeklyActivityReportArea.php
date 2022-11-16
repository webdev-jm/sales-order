<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReportArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_activity_report_id',
        'date',
        'day',
        'location',
        'in_base',
        'remarks',
    ];

    public function weekly_activity_report() {
        return $this->belongsTo('App\Models\WeeklyActivityReport');
    }
}
