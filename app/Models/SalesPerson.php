<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesPerson extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'code'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function accounts() {
        return $this->belongsToMany('App\Models\Account');
    }
}
