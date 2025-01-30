<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountLogin extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'longitude',
        'latitude',
        'accuracy',
        'activities',
        'time_in',
        'time_out'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function sales_orders() {
        return $this->hasMany('App\Models\SalesOrder');
    }
}
