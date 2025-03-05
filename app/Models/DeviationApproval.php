<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class DeviationApproval extends Model
{
    use HasFactory;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }
    
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
