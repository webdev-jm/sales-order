<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_code',
        'area_name'
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function branches() {
        return $this->hasMany('App\Models\Branch');
    }

    public function scopeAreaSearch($query, $search, $limit) {
        if($search != '') {
            $areas = $query->orderBy('id', 'DESC')
            ->where('area_code', 'like', '%'.$search.'%')
            ->orWhere('area_name', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $areas = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $areas;
    }
}
