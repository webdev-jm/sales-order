<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'brand'
    ];

    public function products() {
        return $this->hasMany('App\Models\Product');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }
}
