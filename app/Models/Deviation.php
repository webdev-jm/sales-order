<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deviation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cost_center',
        'reason_for_deviation',
        'status',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function schedules() {
        return $this->hasMany('App\Models\DeviationSchedule');
    }

    public function approvals() {
        return $this->hasMany('App\Models\DeviationApproval');
    }
}
