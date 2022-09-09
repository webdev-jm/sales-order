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
        'account_id',
        'user_id',
        'po_number',
        'order_date',
        'ship_date',
        'ship_description',
        'status',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
