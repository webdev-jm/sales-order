<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'branch_code',
        'branch_name',
        'region',
        'classification',
        'area',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }
}
