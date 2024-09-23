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
        'brand_id',
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
        'status',
        'special_product',
        'bar_code'
    ];

    public function price_codes() {
        return $this->hasMany('App\Models\PriceCode');
    }

    public function accounts() {
        return $this->belongsToMany('App\Models\Account')->withPivot('price_code');
    }

    public function references() {
        return $this->hasMany('App\Models\AccountProductReference');
    }

    public function sales_order_product() {
        return $this->hasMany('App\Models\SalesOrderProduct');
    }

    public function brand() {
        return $this->belongsTo('App\Models\Brand');
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

    public function scopeProductAjax($query, $search) {
        if($search != '') {
            $products = $query->select('id', 'stock_code', 'description', 'size')
            ->limit(5)
            ->get();
        } else {
            $products = $query->select('id', 'stock_code', 'description', 'size')
            ->where('stock_code', 'like', '%'.$search.'%')
            ->orWhere('description', 'like', '%'.$search.'%')
            ->orWhere('size', 'like', '%'.$search.'%')
            ->limit(5)
            ->get();
        }

        $response = [];
        foreach($products as $product) {
            $response[] = [
                'id' => $product->id,
                'text' => '['.$product->stock_code.'] '.$product->description.' '.$product->size
            ];
        }

        return $response;
    }
}
