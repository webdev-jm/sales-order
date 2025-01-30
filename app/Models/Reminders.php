<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminders extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_ids',
        'date',
        'due_date',
        'model_type',
        'model_id',
        'message',
        'link',
        'status',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function model() {
        return $this->morphTo();
    }
}
