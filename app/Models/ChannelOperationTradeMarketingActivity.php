<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationTradeMarketingActivity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_id',
        'paf_number',
        'remarks',
    ];

    public function channel_operation() {
        return $this->belongsTo('App\Models\ChannelOperation');
    }

    public function skus() {
        return $this->hasMany('App\Models\ChannelOperationTradeMarketingActivitySku');
    }
}
