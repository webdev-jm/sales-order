<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChannelOperationTradeDisplay extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'channel_operation_id',
        'planogram',
        'bevi_pricing',
        'osa_bath_actual',
        'osa_bath_target',
        'osa_face_actual',
        'osa_face_target',
        'osa_body_actual',
        'osa_body_target',
        'remarks',
    ];

    public function channel_operation() {
        return $this->belongsTo('App\Models\ChannelOperation');
    }
}
