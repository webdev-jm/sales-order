<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'district_code',
        'district_name'
    ];

    public function users() {
        return $this->belongsToMany('App\Models\User');
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
