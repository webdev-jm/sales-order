<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_name'
    ];

    public function branches() {
        return $this->hasMany('App\Models\Branch');
    }

    public function scopeRegionSearch($query, $search, $limit) {
        if($search != '') {
            $regions = $query->orderBy('id', 'DESC')
            ->where('region_name', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $regions = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $regions;
    }
}
