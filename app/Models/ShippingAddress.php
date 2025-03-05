<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ShippingAddress extends Model
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
                $qry->where('address_code', 'like', '%'.$search.'%')
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

    public function scopeShippingAddressAjax($query, $search, $account_id) {
        $shipping_addresses = $query->select('id', 'address_code', 'ship_to_name')
            ->when(!empty(trim($search)), function($qry) use($search) {
                $qry->where('address_code', 'like', '%'.$search.'%')
                    ->orWhere('ship_to_name', 'like', '%'.$search.'%');
            })
            ->limit(5)->get();

        $response = [];
        foreach($shipping_addresses as $ship_address) {
            $response[] = [
                'id' => $ship_address->id,
                'text' => '['.$ship_address->address_code.'] '.$ship_address->ship_to_name
            ];
        }

        return $response;
    }
}
