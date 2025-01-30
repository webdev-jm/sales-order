<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBranchScheduleApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_branch_schedule_id',
        'user_id',
        'status',
        'remarks',
    ];

    public function user_branch_schedule() {
        return $this->belongsTo('App\Models\UserBranchSchedule');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
