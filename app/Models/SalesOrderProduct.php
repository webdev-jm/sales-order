<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'part',
        'total_quantity',
        'total_sales',
    ];

    public function sales_order() {
        return $this->belongsTo('App\Models\SalesOrder');
    }

    public function product_uoms() {
        return $this->hasMany('App\Models\SalesOrderProductUom');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
}
