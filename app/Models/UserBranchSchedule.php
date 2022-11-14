<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBranchSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'date',
        'status',
        'reschedule_date',
        'objective',
        'source'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function approvals() {
        return $this->hasMany('App\Models\UserBranchScheduleApproval');
    }
}
