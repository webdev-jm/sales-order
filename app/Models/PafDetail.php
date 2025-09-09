<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class PafDetail extends Model
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
        'sku_code',
        'sku_description',
        'brand',
        'category',
        'quantity',
    ];

    public function paf() {
        return $this->belongsTo('App\Models\Paf', 'PAFNo', 'PAFNo');
    }
}
