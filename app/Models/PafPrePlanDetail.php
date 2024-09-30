<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PafPrePlanDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'paf_pre_plan_id',
        'product_id',
        'type',
        'components',
        'stock_code',
        'description',
        'price_code',
        'brand',
        'quantity',
        'branch',
        'GlCode',
        'IO',
        'amount',
    ];

    public function paf_pre_plan() {
        return $this->belongsTo('App\Models\PafPrePlan');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
}
