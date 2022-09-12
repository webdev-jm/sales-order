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
}
