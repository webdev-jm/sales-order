<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PafApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'paf_id',
        'user_id',
        'status',
        'remarks',
    ];

    public function paf() {
        return $this->belongsTo('App\Models\Paf');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
