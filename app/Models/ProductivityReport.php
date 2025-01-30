<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductivityReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'week',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
