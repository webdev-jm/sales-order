<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PafBrandApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'paf_id',
        'brand_id',
        'remarks',
    ];

    public function paf() {
        return $this->belongsTo('App\Models\Paf');
    }

    public function brand() {
        return $this->belongsTo('App\Models\Brand');
    }
}
