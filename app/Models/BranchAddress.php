<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class BranchAddress extends Model
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
        'branch_id',
        'latitude',
        'longitude',
        'street1',
        'street2',
        'city',
        'state',
        'zip',
        'country',
        'address',
    ];

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }
}
