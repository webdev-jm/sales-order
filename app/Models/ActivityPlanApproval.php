<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPlanApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'activity_plan_id',
        'status',
        'remarks'
    ];

    public function activity_plan() {
        return $this->belongsTo('App\Models\ActivityPlan');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
