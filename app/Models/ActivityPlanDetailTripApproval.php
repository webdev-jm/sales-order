<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPlanDetailTripApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'activity_plan_detail_trip_id',
        'status',
        'remarks',
    ];

    public function trip() {
        return $this->belongsTo('App\Models\ActivityPlanDetailTrip');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
