<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    public function discount() {
        return $this->belongsTo('App\Models\Discount');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function sales_person() {
        return $this->hasMany('App\Models\SalesPerson');
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
}
