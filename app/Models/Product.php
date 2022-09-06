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
        'brand',
        'alternative_code',
        'stock_uom1',
        'stock_uom2',
        'stock_uom3',
        'uom_price1',
        'uom_price2',
        'uom_price3',
    ];
}
