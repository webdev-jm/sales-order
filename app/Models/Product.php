<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'stock_code',
        'description',
        'size',
        'category',
        'product_class',
        'brand',
        'core_group',
        'stock_uom',
        'order_uom',
        'other_uom',
        'order_uom_conversion',
        'other_uom_conversion',
        'order_uom_operator',
        'other_uom_operator',
    ];

    public function price_code() {
        return $this->hasMany('App\Models\PriceCode');
    }

    public function scopeProductSearch($query, $search, $limit) {
        if($search != '') {
            $products = $query->orderBy('id', 'DESC')
            ->where('stock_code', 'like', '%'.$search.'%')
            ->orWhere('description', 'like', '%'.$search.'%')
            ->orWhere('size', 'like', '%'.$search.'%')
            ->orWhere('brand', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $products = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $products;
    }
}
