<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_login_id',
        'shipping_address_id',
        'control_number',
        'po_number',
        'sales_order',
        'order_date',
        'ship_date',
        'shipping_instruction',
        'ship_to_name',
        'ship_to_building',
        'ship_to_street',
        'ship_to_city',
        'ship_to_postal',
        'status',
        'total_quantity',
        'total_sales',
        'grand_total',
        'po_value'
    ];

    public function account_login() {
        return $this->belongsTo('App\Models\AccountLogin');
    }

    public function order_products() {
        return $this->hasMany('App\Models\SalesOrderProduct');
    }

    public function scopeSalesOrderSearch($query, $search, $logged_account,$limit) {
        if($search != '') {
            $sales_orders = $query->orderBy('id', 'DESC')
            ->whereHas('account_login', function($qry) use($logged_account) {
                $qry->where('account_id', $logged_account->account_id);
            })
            ->where(function($qry) use($search) {
                $qry->where('control_number', 'like', '%'.$search.'%')
                ->orWhere('po_number', 'like', '%'.$search.'%')
                ->orWhere('order_date', 'like', '%'.$search.'%')
                ->orWhere('ship_date', 'like', '%'.$search.'%')
                ->orWhere('ship_to_name', 'like', '%'.$search.'%')
                ->orWhere('status', 'like', '%'.$search.'%');
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $sales_orders = $query->orderBy('id', 'DESC')
            ->whereHas('account_login', function($qry) use($logged_account) {
                $qry->where('account_id', $logged_account->account_id);
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $sales_orders;
    }

    public function scopeSalesOrderListSearch($query, $search, $limit) {
        // if($search != '') {
        //     $sales_orders = $query->orderBy('control_number', 'DESC')
        //     ->whereHas('account_login', function($qry) use($search) {
        //         $qry->whereHas('account', function($qry1) use($search) {
        //             if(auth()->user()->id == 1) { // admin
        //                 $qry1->where(function($qry2) use ($search) {
        //                     $qry2->where('account_code', 'like', '%'.$search.'%')
        //                     ->orWhere('short_name', 'like', '%'.$search.'%');
        //                 });
        //             } else {
        //                 $qry1->where(function($qry2) {
        //                     $qry2->where('account_code', 'like', '%'.$search.'%')
        //                     ->orWhere('short_name', 'like', '%'.$search.'%');
        //                 })
        //                 ->whereHas('users', function($qry2) {
        //                     $qry2->where('id', auth()->user()->id);
        //                 });
        //             }
        //         });
        //     })
        //     ->where(function($qry) use ($search) {
        //         $qry->where('control_number', 'like', '%'.$search.'%')
        //         ->orWhere('po_number', 'like', '%'.$search.'%')
        //         ->orWhere('order_date', 'like', '%'.$search.'%')
        //         ->orWhere('ship_date', 'like', '%'.$search.'%')
        //         ->orWhere('ship_to_name', 'like', '%'.$search.'%')
        //         ->orWhere('status', 'like', '%'.$search.'%');
        //     })
        //     ->paginate($limit)->onEachSide(1)->appends(request()->query());
        // } else {
        //     $sales_orders = $query->orderBy('control_number', 'DESC')
        //     ->whereHas('account_login', function($qry) use($search) {
        //         $qry->whereHas('account', function($qry1) use($search) {
        //             if(auth()->user()->id == 1) { // admin

        //             } else {
        //                 $qry1->whereHas('users', function($qry2) {
        //                     $qry2->where('id', auth()->user()->id);
        //                 });
        //             }
        //         });
        //     })
        //     ->paginate($limit)->onEachSide(1)->appends(request()->query());
        // }

        $sales_orders = $query->orderBy('control_number', 'DESC')
        ->whereHas('account_login', function($qry) use($search) {
            $qry->whereHas('account', function($qry1) use($search) {
                if(auth()->user()->id == 1) { // admin

                } else {
                    $qry1->whereHas('users', function($qry2) {
                        $qry2->where('id', auth()->user()->id);
                    });
                }
            });
        })
        ->paginate($limit)->onEachSide(1)->appends(request()->query());

        return $sales_orders;
    }
}
