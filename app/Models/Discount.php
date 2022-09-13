<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'discount_code',
        'description',
        'discount_1',
        'discount_2',
        'discount_3',
    ];

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function scopeDiscountSearch($query, $search, $limit) {
        if($search != '') {
            $discounts = $query->orderBy('id', 'DESC')
            ->whereHas('company', function($qry) use($search) {
                $qry->where('name', 'like', '%'.$search.'%');
            })
            ->orWhere('discount_code', 'like', '%'.$search.'%')
            ->orWhere('description', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $discounts = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $discounts;
    }
}
