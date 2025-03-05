<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ActivityPlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'objectives',
        'status'
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function reminders() {
        return $this->morphMany('\App\Models\Reminders', 'model');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function details() {
        return $this->hasMany('App\Models\ActivityPlanDetail');
    }

    public function approvals() {
        return $this->hasMany('App\Models\ActivityPlanApproval');
    }

    public function scopeActivityPlanSearch($query, $search, $limit) {
        if($search != '') {
            $activity_plans = $query->orderBy('id', 'DESC')
            ->where('month', 'like', '%'.$search.'%')
            ->orWhere('year', 'like', '%'.$search.'%')
            ->orWhere('status', 'like', '%'.$search.'%')
            ->orWhereHas('user', function($qry) use($search) {
                $qry->where('firstname', 'like', '%'.$search.'%')
                ->orWhere('lastname', 'like', '%'.$search.'%');
            })
            ->paginate($limit, ['*'], 'activity-plan-page')->onEachSide(1)->appends(request()->query());
        } else {
            $activity_plans = $query->orderBy('id', 'DESC')
            ->paginate($limit, ['*'], 'activity-plan-page')->onEachSide(1)->appends(request()->query());
        }

        return $activity_plans;
    }

    public function scopeActivityPlanSearchRestricted($query, $search, $limit, $subordinate_ids) {
        if($search != '') {
            $activity_plans = $query->orderBy('id', 'DESC')
            ->where(function($qry) use($subordinate_ids) {
                $qry->where('user_id', auth()->user()->id)
                ->orWhereIn('user_id', $subordinate_ids);
            })
            ->where(function($qry) use($search) {
                $qry->where('month', 'like', '%'.$search.'%')
                ->orWhere('year', 'like', '%'.$search.'%')
                ->orWhere('status', 'like', '%'.$search.'%')
                ->orWhereHas('user', function($qry) use($search) {
                    $qry->where('firstname', 'like', '%'.$search.'%')
                    ->orWhere('lastname', 'like', '%'.$search.'%');
                });
            })
            ->paginate($limit, ['*'], 'activity-plan-page')->onEachSide(1)->appends(request()->query());
        } else {
            $activity_plans = $query->orderBy('id', 'DESC')
            ->where(function($qry)  use($subordinate_ids) {
                $qry->where('user_id', auth()->user()->id)
                ->orWhereIn('user_id', $subordinate_ids);
            })
            ->paginate($limit, ['*'], 'activity-plan-page')->onEachSide(1)->appends(request()->query());
        }

        return $activity_plans;
    }
}
