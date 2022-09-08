<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'code',
        'selling_price',
        'price_basis',
    ];

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
}
