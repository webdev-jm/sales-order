<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class PuregoldStoreMap extends Model
{
    use HasFactory;

    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default'));
    }

    protected $fillable = [
        'store_code',
        'bevi_code',
    ];
}
