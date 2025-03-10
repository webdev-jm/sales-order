<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Session;

class Invoice extends Model
{
    use HasFactory;
    
    protected $table = 'invoices';

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }
}
