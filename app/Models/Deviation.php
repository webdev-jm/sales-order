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
        'date',
        'reason_for_deviation',
        'status',
    ];

    public function reminders() {
        return $this->morphMany('App\Models\Reminders', 'model');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function schedules() {
        return $this->hasMany('App\Models\DeviationSchedule');
    }

    public function approvals() {
        return $this->hasMany('App\Models\DeviationApproval');
    }

    public function scopeDeviationSearch($query, $search, $limit) {
        $subordinate_ids = auth()->user()->getSubordinateIds();
        $ids = [];
        foreach($subordinate_ids as $level => $id_arr) {
            foreach($id_arr as $id) {
                $ids[] = $id;
            }
        }

        if($search != '') {
            $deviations = $query->orderBy('created_at', 'DESC')
            ->where(function($qry) use($ids) {
                $qry->where('user_id', auth()->user()->id)
                ->orWhereIn('user_id', $ids);
            })
            ->where(function($qry) use($search) {
                $qry->where('cost_center', 'like', '%'.$search.'%')
                ->orWhere('date', 'like', '%'.$search.'%')
                ->orWhere('status', 'like', '%'.$search.'%')
                ->orWhereHas('user', function($qry1) use($search) {
                    $qry1->where('firstname', 'like', '%'.$search.'%')
                    ->orWhere('lastname', 'like', '%'.$search.'%');
                });
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $deviations = $query->orderBy('created_at', 'DESC')
            ->where(function($qry) use($ids) {
                $qry->where('user_id', auth()->user()->id)
                ->orWhereIn('user_id', $ids);
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $deviations;
    }
}
