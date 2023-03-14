<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class PriceCode extends Model
{
    use HasFactory, SoftDeletes;

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

    public function scopePriceCodeSearch($query, $search, $code, $limit) {
        if($search != '' || $code != '') {
            $price_codes = $query->orderBy('id', 'DESC');

            if($search != '') {
                $price_codes->where(function($qry) use($search) {
                    $qry->whereHas('company', function($qry1) use ($search) {
                        $qry1->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('product', function($qry1) use ($search) {
                        $qry1->where('stock_code', 'like', '%'.$search.'%');
                    })
                    ->orWhere('selling_price', 'like', '%'.$search.'%');
                });
            }

            if($code != '') {
                $price_codes->where('code', $code);
            }

            $price_codes = $price_codes->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $price_codes = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $price_codes;
    }
}
