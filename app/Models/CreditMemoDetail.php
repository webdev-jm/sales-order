<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class CreditMemoDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'credit_memo_id',
        'product_id',
        'credit_note_number',
        'warehouse',
        'order_quantity',
        'order_uom',
        'price',
        'price_uom',
        'unit_cost',
        'ship_quantity',
        'stock_quantity_to_ship',
        'stocking_uom',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function credit_memo() {
        return $this->belongsTo('App\Models\CreditMemo', 'credit_memo_id', 'id');
    }

    public function cm_bins() {
        return $this->hasMany('App\Models\CreditMemoDetailBin', 'credit_memo_detail_id', 'id');
    }
}
