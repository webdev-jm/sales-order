<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReportCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_activity_report_id',
        'beginning_ar',
        'due_for_collection',
        'beginning_hanging_balance',
        'target_reconciliations',
        'week_to_date',
        'month_to_date',
        'month_target',
        'balance_to_sell',
    ];

    public function weekly_activity_report() {
        return $this->belongsTo('App\Models\WeeklyActivityReport');
    }
}
