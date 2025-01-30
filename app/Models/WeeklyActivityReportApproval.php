<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReportApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weekly_activity_report_id',
        'status',
        'remarks'
    ];

    public function weekly_activity_report() {
        return $this->belongsTo('App\Models\WeeklyActivityReport');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
