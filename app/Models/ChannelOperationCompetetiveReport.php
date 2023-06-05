<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationCompetetiveReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_id',
        'company_name',
        'product_description',
        'srp',
        'type_of_promotion',
        'impact_to_our_product',
    ];

    public function channel_operation() {
        return $this->belongsTo('App\Models\ChannelOperation');
    }
}
