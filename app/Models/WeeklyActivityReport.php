<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class WeeklyActivityReport extends Model
{
    use HasFactory;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'user_id',
        'area_id',
        'date_from',
        'date_to',
        'accounts_visited',
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

    public function areas() {
        return $this->hasMany('App\Models\WeeklyActivityReportArea');
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
            ->when(!auth()->user()->hasRole('superadmin') || !auth()->user()->hasPermissionTo('war approve'), function($query) use($ids) {
                $query->where(function($qry) use ($ids) {
                    $qry->where('user_id', auth()->user()->id)
                    ->orWhereIn('user_id', $ids);
                });
            })
            ->when(!empty($search), function($query) use ($search) {
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