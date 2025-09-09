<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Paf extends Model
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
        'PAFNo',
        'account_code',
        'account_name',
        'title',
        'start_date',
        'end_date',
        'support_type',
    ];

    public function paf_details() {
        return $this->hasMany('App\Models\PafDetail', 'PAFNo', 'PAFNo');
    }
}
