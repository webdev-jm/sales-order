<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderProductUomPAF extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sales_order_product_uom_id',
        'paf_number',
        'uom',
        'quantity',
    ];

    public function sales_order_product_uom()
    {
        return $this->belongsTo('App\Models\SalesOrderProductUom');
    }
}
