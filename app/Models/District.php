<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class District extends Model
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
        'district_code',
        'district_name'
    ];

    public function users() {
        return $this->belongsToMany('App\Models\User')->withTrashed();
    }

    public function territories() {
        return $this->hasMany('App\Models\Territory');
    }

    public function scopeDistrictSearch($query, $search, $limit) {
        if($search != '') {
            $districts = $query->orderBy('id', 'DESC')
                ->where('district_code', 'like', '%'.$search.'%')
                ->orWhere('district_name', 'like', '%'.$search.'%')
                ->paginate($limit)
                ->onEachSide(1)
                ->appends(request()->query());
        } else {
            $districts = $query->orderBy('id', 'DESC')
                ->paginate($limit)
                ->onEachSide(1)
                ->appends(request()->query());
        }

        return $districts;
    }
}
