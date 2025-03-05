<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ProductivityReportData extends Model
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
        'productivity_report_id',
        'branch_id',
        'classification_id',
        'salesman_id',
        'salesman_location_id',
        'date',
        'visited',
        'sales',
    ];

    public function productivity_report() {
        return $this->belongsTo('App\Models\ProductivityReport');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function classification() {
        return $this->belongsTo('App\Models\Classification');
    }

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }

    public function salesman_location() {
        return $this->belongsTo('App\Models\SalesmenLocation');
    }
}
