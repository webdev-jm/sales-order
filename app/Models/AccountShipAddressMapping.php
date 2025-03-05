<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class AccountShipAddressMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'shipping_address_id',
        'reference1',
        'reference2',
        'reference3',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function shipping_address() {
        return $this->belongsTo('App\Models\ShippingAddress');
    }
}
