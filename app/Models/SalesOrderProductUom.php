<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderProductUom extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sales_order_product_id',
        'uom',
        'quantity',
        'uom_total',
        'uom_total_less_disc'
    ];
}
