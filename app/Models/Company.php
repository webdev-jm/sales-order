<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function price_codes() {
        return $this->hasMany('App\Models\PriceCode');
    }

    public function scopeCompanySearch($query, $search) {
        if($search != '') {
            $companies = $query->orderBy('id', 'DESC')
            ->where('name', 'like', '%'.$search.'%')
            ->paginate(10)->onEachSide(1)->appends(request()->query());
        } else {
            $companies = $query->orderBy('id', 'DESC')
            ->paginate(10)->onEachSide(1)->appends(request()->query());
        }

        return $companies;
    }
}
