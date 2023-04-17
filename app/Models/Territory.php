<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Territory extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->belongsTo('App\Models\User');
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
