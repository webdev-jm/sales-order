<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationTradeMarketingActivitySku extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_trade_marketing_activity_id',
        'paf_detail_id',
        'sku_code',
        'sku_description',
        'brand',
        'actual',
        'target_maxcap',
    ];

    public function trade_marketing_activity() {
        return $this->belongsTo('App\Models\ChannelOperationTradeMarketingActivity');
    }
}
