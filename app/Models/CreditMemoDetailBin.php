<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;


class CreditMemoDetailBin extends Model
{
    use HasFactory;
    USE SoftDeletes;

    protected $fillable = [
        'credit_memo_detail_id',
        'lot_number',
        'bin_location',
        'quantity',
        'uom',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }
}
