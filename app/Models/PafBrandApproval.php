<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class PafBrandApproval extends Model
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
        'paf_id',
        'brand_id',
        'remarks',
    ];

    public function paf() {
        return $this->belongsTo('App\Models\Paf');
    }

    public function brand() {
        return $this->belongsTo('App\Models\Brand');
    }
}
