<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPlanDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_id',
        'user_id',
        'branch_id',
        'day',
        'date',
        'exact_location',
        'activity'
    ];

    public function activity_plan() {
        return $this->belongsTo('App\Models\ActivityPlan');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }
}
