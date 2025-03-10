<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Session;

class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'invoice_term_id',
        'company_id',
        'discount_id',
        'account_code',
        'account_name',
        'short_name',
        'line_discount_code',
        'price_code',
        'ship_to_address1',
        'ship_to_address2',
        'ship_to_address3',
        'postal_code',
        'tax_number',
        'on_hold',
        'sales_order_uom',
        'po_process_date',
        'po_prefix',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function discount() {
        return $this->belongsTo('App\Models\Discount');
    }

    public function branches() {
        return $this->hasMany('App\Models\Branch');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User')->withTrashed();
    }

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function sales_person() {
        return $this->hasMany('App\Models\SalesPerson');
    }

    public function sales_people() {
        return $this->belongsToMany('App\Models\SalesPerson');
    }

    public function account_logins() {
        return $this->hasMany('App\Models\AccountLogin');
    }

    public function shipping_addresses() {
        return $this->hasMany('App\Models\ShippingAddress');
    }

    public function products() {
        return $this->belongsToMany('App\Models\Product')->withPivot('price_code');
    }

    public function references() {
        return $this->hasMany('App\Models\AccountProductReference');
    }

    public function scopeAccountSearch($query, $search, $limit) {
        if($search != '') {
            $accounts = $query->orderBy('id', 'DESC')
            ->where('account_code', 'like', '%'.$search.'%')
            ->orWhere('account_name', 'like', '%'.$search.'%')
            ->orWhere('short_name', 'like', '%'.$search.'%')
            ->orWhereHas('company', function($qry) use($search) {
                $qry->where('name', 'like', '%'.$search.'%');
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $accounts = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $accounts;
    }

    public function scopeLoginAccountSearch($query, $search, $limit) {
        if($search != '') {
            $accounts = $query->orderBy('account_code', 'ASC')
            ->whereHas('account_logins')
            ->where(function($qry) use ($search) {
                $qry->where('account_code', 'like', '%'.$search.'%')
                ->orWhere('account_name', 'like', '%'.$search.'%')
                ->orWhere('short_name', 'like', '%'.$search.'%')
                ->orWhereHas('company', function($qry) use($search) {
                    $qry->where('name', 'like', '%'.$search.'%');
                });
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $accounts = $query->orderBy('account_code', 'ASC')
            ->whereHas('account_logins')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $accounts;
    }

    public function scopeAccountAjax($query, $search) {
        if($search == '') {
            $accounts = $query->select('id', 'account_code', 'short_name')->limit(5)->get();
        } else {
            $accounts = $query->select('id', 'account_code', 'short_name')
            ->where('account_code', 'like', '%'.$search.'%')
            ->orWhere('short_name', 'like', '%'.$search.'%')
            ->limit(5)->get();
        }

        $response = [];
        foreach($accounts as $account) {
            $response[] = [
                'id' => $account->id,
                'text' => '['.$account->account_code.'] '.$account->short_name
            ];
        }

        return $response;
    }
}
