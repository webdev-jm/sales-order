<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'code',
        'selling_price',
        'price_basis',
    ];

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }

    public function scopePriceCodeSearch($query, $search, $limit) {
        if($search != '') {
            $price_codes = $query->orderBy('id', 'DESC')
            ->whereHas('company', function($qry) use ($search) {
                $qry->where('name', 'like', '%'.$search.'%');
            })
            ->orWhereHas('product', function($qry) use ($search) {
                $qry->where('stock_code', 'like', '%'.$search.'%');
            })
            ->orWhere('code', 'like', '%'.$search.'%')
            ->orWhere('selling_price', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $price_codes = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $price_codes;
    }
}
