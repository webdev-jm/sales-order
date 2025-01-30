<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPlanDetailTripDestination extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_detail_trip_id',
        'activity_plan_detail_id',
        'user_id',
        'from',
        'to',
        'departure',
        'return',
    ];

    public function trip() {
        return $this->belongsTo('App\Models\ActivityPlanDetailTrip', 'activity_plan_detail_trip_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
