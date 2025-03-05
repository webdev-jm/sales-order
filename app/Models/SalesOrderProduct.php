<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class SalesOrderProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

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
