<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class SalesmenLocation extends Model
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
        'salesman_id',
        'province',
        'city',
    ];

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }

    public function productivity_report_data() {
        return $this->hasMany('App\Models\ProductivityReportData');
    }
}
