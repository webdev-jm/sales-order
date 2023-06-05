<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationExtraDisplay extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_id',
        'location',
        'rate_per_month',
        'amount',
    ];

    public function channel_operation() {
        return $this->belongsTo('App\Models\ChannelOperation');
    }
}
