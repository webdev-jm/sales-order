<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class SalesOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     * This supports multi-tenant deployments where each client has a separate database.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default'));
    }

    protected $fillable = [
        'account_login_id',
        'shipping_address_id',
        'control_number',
        'po_number',
        'paf_number',
        'reference',
        'upload_status',
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
        'po_value',
    ];

    public function account_login()
    {
        return $this->belongsTo(AccountLogin::class);
    }

    public function order_products()
    {
        return $this->hasMany(SalesOrderProduct::class);
    }

    /**
     * Scope for the account-facing index page. Filters by the logged-in account
     * and optionally by a search string across common SO fields.
     */
    public function scopeSalesOrderSearch($query, $search, $logged_account, $limit)
    {
        return $query
            ->orderBy('id', 'DESC')
            ->whereHas('account_login', function ($qry) use ($logged_account) {
                $qry->where('account_id', $logged_account->account_id);
            })
            ->when($search != '', function ($qry) use ($search) {
                $qry->where(function ($q) use ($search) {
                    $q->where('control_number', 'like', '%' . $search . '%')
                        ->orWhere('po_number', 'like', '%' . $search . '%')
                        ->orWhere('order_date', 'like', '%' . $search . '%')
                        ->orWhere('ship_date', 'like', '%' . $search . '%')
                        ->orWhere('ship_to_name', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                });
            })
            ->paginate($limit)
            ->onEachSide(1)
            ->appends(request()->query());
    }

    /**
     * Scope for the admin list page. Searches across SO fields and related
     * account/user names. No account filter â€” shows orders for all accounts.
     */
    public function scopeSalesOrderListSearch($query, $search, $limit)
    {
        return $query
            ->orderBy('control_number', 'DESC')
            ->when($search != '', function ($q) use ($search) {
                $q->where(function ($qry) use ($search) {
                    $qry->where('control_number', 'like', '%' . $search . '%')
                        ->orWhere('po_number', 'like', '%' . $search . '%')
                        ->orWhere('order_date', 'like', '%' . $search . '%')
                        ->orWhere('ship_date', 'like', '%' . $search . '%')
                        ->orWhere('ship_to_name', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                })->orWhereHas('account_login.account', function ($qry) use ($search) {
                    $qry->where('account_code', 'like', '%' . $search . '%')
                        ->orWhere('short_name', 'like', '%' . $search . '%')
                        ->orWhereHas('users', function ($qry2) use ($search) {
                            $qry2->where('firstname', 'like', '%' . $search . '%')
                                ->orWhere('lastname', 'like', '%' . $search . '%');
                        });
                });
            })
            ->paginate($limit)
            ->onEachSide(1)
            ->appends(request()->query());
    }
}

