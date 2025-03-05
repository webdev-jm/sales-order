<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class BranchLoginActivity extends Model
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
        'branch_login_id',
        'activity_id',
        'remarks',
    ];

    public function branch_login() {
        return $this->belongsTo('App\Models\BranchLogin');
    }

    public function activity() {
        return $this->belongsTo('App\Models\Activity');
    }
}
