<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Reminders extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

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
