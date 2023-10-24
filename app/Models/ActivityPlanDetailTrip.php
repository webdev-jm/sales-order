<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPlanDetailTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_plan_detail_id',
        'trip_number',
        'departure',
        'arrival',
        'reference_number',
        'qr_code_path',
        'attachment_path',
    ];

    public function activity_plan_detail() {
        return $this->belongsTo('App\Models\ActivityPlanDetail');
    }
}
