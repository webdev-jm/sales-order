<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviationApproval extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'deviation_id',
        'user_id',
        'status',
        'remarks',
    ];

    public function deviation() {
        return $this->belongsTo('App\Models\Deviation');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
