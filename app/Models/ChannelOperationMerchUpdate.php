<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationMerchUpdate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_id',
        'status',
        'actual',
        'target',
        'days_of_gaps',
        'sales_opportunities',
        'remarks',
    ];

    public function channel_operation() {
        return $this->belongsTo('App\Models\ChannelOperation');
    }
}
