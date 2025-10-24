<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class CreditMemo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'user_id',
        'credit_memo_reason_id',
        'invoice_number',
        'po_number',
        'so_number',
        'cm_date',
        'ship_date',
        'ship_code',
        'ship_name',
        'shipping_instruction',
        'ship_address1',
        'ship_address2',
        'ship_address3',
        'ship_address4',
        'ship_address5',
        'status',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function cm_details() {
        return $this->hasMany('App\Models\CreditMemoDetail', 'credit_memo_id', 'id');
    }

    public function account() {
        return $this->belongsTo('App\Models\Account', 'account_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function reason() {
        return $this->belongsTo('App\Models\CreditMemoReason', 'credit_memo_reason_id', 'id');
    }
}
