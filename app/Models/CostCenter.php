<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class CostCenter extends Model
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
        'company_id',
        'user_id',
        'cost_center'
    ];

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function scopeCostCenterSearch($query, $search, $limit) {
        if($search != '') {
            $cost_centers = $query->orderBy('company_id', 'ASC')
            ->where('cost_center', 'like', '%'.$search.'%')
            ->orWhereHas('company', function($qry) use($search) {
                $qry->where('name', 'like', '%'.$search.'%');
            })
            ->orWhereHas('user', function($qry) use($search) {
                $qry->where('firstname', 'like', '%'.$search.'%');
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $cost_centers = $query->orderBy('company_id', 'ASC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $cost_centers;
    }
}
