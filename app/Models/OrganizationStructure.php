<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class OrganizationStructure extends Model
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
        'user_id',
        'job_title_id',
        'reports_to_id',
        'type'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function job_title() {
        return $this->belongsTo('App\Models\JobTitle');
    }
}
// k0J13oS4n?!