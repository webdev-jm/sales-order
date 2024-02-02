<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPlanDetailTripAttachment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'activity_plan_detail_trip_id',
        'title',
        'description',
        'url'
    ];

    public function trip() {
        return $this->belongsTo('App\Models\ActivityPlanDetailTrip');
    }
}
