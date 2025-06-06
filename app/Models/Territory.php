<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Territory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'district_id',
        'user_id',
        'territory_code',
        'territory_name'
    ];

    public function district() {
        return $this->belongsTo('App\Models\District');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function branches() {
        return $this->belongsToMany('App\Models\Branch');
    }

    public function scopeTerritorySearch($query, $search, $limit) {
        if($search != '') {
            $territories = $query->orderBy('id', 'DESC')
            ->where(function($qry) use($search) {
                $qry->where('territory_code', 'like', '%'.$search.'%')
                ->orWhere('territory_name', 'like', '%'.$search.'%');
            })
            ->orWhereHas('user', function($qry) use($search) {
                $qry->where('firstname', 'like', '%'.$search.'%')
                ->orWhere('lastname', 'like', '%'.$search.'%');
            })
            ->orWhereHas('district', function($qry) use($search) {
                $qry->where('district_code', 'like', '%'.$search.'%');
            })
            ->paginate($limit)->onEachSide(1)
            ->appends(request()->query());
        } else {
            $territories = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)
            ->appends(request()->query());
        }

        return $territories;
    }
}
