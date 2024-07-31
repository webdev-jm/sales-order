<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function shipping_address() {
        return $this->belongsTo('App\Models\ShippingAddress');
    }
}
