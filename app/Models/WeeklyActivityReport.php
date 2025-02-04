<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyActivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area_id',
        'date_from',
        'date_to',
        'week_number',
        'date_submitted',
        'objectives',
        'highlights',
        'status',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function area() {
        return $this->belongsTo('App\Models\Area');
    }

    public function objectives() {
        return $this->hasMany('App\Models\WeeklyActivityReportObjective');
    }

    public function areas() {
        return $this->hasMany('App\Models\WeeklyActivityReportArea');
    }

    public function collection() {
        return $this->hasOne('App\Models\WeeklyActivityReportCollection');
    }

    public function action_plans() {
        return $this->hasMany('App\Models\WeeklyActivityReportActionPlan');
    }

    public function activities() {
        return $this->hasMany('App\Models\WeeklyActivityReportActivity');
    }

    public function approvals() {
        return $this->hasMany('App\Models\WeeklyActivityReportApproval');
    }

    public function scopeWeeklyActivityReportSearch($query, $search, $limit, $subordinate_ids) {
        $ids = [];
        foreach($subordinate_ids as $level => $id_arr) {
            foreach($id_arr as $id) {
                $ids[] = $id;
            }
        }

        $weekly_activity_reports = $query->orderBy('id', 'DESC')
            ->when(!auth()->user()->hasRole('superadmin'), function($query) {
                $query->where(function($qry) use ($ids) {
                    $qry->where('user_id', auth()->user()->id)
                    ->orWhereIn('user_id', $ids);
                });
            })
            ->when(!empty($search), function($query) {
                $query->where(function($qry) use($search) {
                    $qry->whereHas('user', function($qry) use ($search) {
                        $qry->where('firstname', 'like', '%'.$search.'%')
                        ->orWhere('lastname', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('area', function($qry1) use($search) {
                        $qry1->where('area_code', 'like', '%'.$search.'%')
                        ->orWhere('area_name', 'like', '%'.$search.'%');
                    })
                    ->orWhere('date_submitted', 'like', '%'.$search.'%')
                    ->orWhere('date_from', 'like', '%'.$search.'%')
                    ->orWhere('date_to', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
                });
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());

        return $weekly_activity_reports;
    }
}