<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_code',
        'area_name'
    ];

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
