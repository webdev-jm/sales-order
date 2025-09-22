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
        'lot_number',
        'quantity',
        'uom',
        'unit_price',
        'amount',
        'expiry_date',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }
}
