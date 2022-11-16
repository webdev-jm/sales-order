<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area_id',
        'date_from',
        'date_to',
        'week_number',
        'date_submitted',
        'highlights',
        'status',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function area() {
        return $this->belongsTo('App\Models\Area');
    }

    public function objectives() {
        return $this->haMany('App\Models\WeeklyActivityReportObjective');
    }

    public function areas() {
        return $this->hasMany('App\Models\WeeklyActivityReportArea');
    }

    public function collections() {
        return $this->hasOne('App\Models\WeeklyActivityReportCollection');
    }

    public function action_plans() {
        return $this->hasMany('App\Models\WeeklyActivityReportActionPlan');
    }

    public function activities() {
        return $this->hasMany('App\Models\WeeklyActivityReportActivity');
    }
}