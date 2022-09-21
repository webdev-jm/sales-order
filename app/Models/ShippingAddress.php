<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'address_code',
        'ship_to_name',
        'building',
        'street',
        'city',
        'tin',
        'postal',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function scopeShippingAddressSearch($query, $search, $account_id, $limit) {
        if($search != '') {
            $shipping_addresses = $query->orderBy('address_code', 'DESC')
            ->where('account_id', $account_id)
            ->where(function($qry) use($search) {
                $qry->where('account_code', 'like', '%'.$search.'%')
                ->orWhere('ship_to_name', 'like', '%'.$search.'%')
                ->orWhere('building', 'like', '%'.$search.'%')
                ->orWhere('street', 'like', '%'.$search.'%')
                ->orWhere('city', 'like', '%'.$search.'%')
                ->orWhere('tin', 'like', '%'.$search.'%')
                ->orWhere('postal', 'like', '%'.$search.'%');
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $shipping_addresses = $query->orderBy('address_code', 'DESC')
            ->where('account_id', $account_id)
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $shipping_addresses;
    }
}
