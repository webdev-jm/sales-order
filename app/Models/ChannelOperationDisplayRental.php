<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationDisplayRental extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_id',
        'status',
        'location',
        'stocks_displayed',
        'remarks',
    ];

    public function channel_operation() {
        return $this->belongsTo('App\Models\ChannelOperation');
    }
}
