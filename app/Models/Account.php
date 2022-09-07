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
        'company_id',
        'discount_id',
        'account_code',
        'account_name',
        'short_name'
    ];

    public function discount() {
        return $this->belongsTo('App\Models\Discount');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }
}
