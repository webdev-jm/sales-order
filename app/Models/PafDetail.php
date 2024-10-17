<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PafDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'paf_id',
        'product_id',
        'branch_id',
        'type',
        'branch',
        'amount',
        'expense',
        'quantity',
        'srp',
        'percentage',
        'status',
    ];

    public function paf() {
        return $this->belongsTo('App\Models\Paf');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
}
