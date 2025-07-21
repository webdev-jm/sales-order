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
        'paf_id',
        'product_id',
        'components',
        'line',
        'stock_code',
        'description',
        'brand',
        'category',
    ];

    public function paf() {
        return $this->belongsTo('App\Models\Paf');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }
}
