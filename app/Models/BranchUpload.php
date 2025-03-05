<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class BranchUpload extends Model
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
        'account_code',
        'region',
        'classification',
        'branch_name',
        'branch_code',
        'account_group',
        'inventory',
        'type',
        'area_code',
        'area_name',
        'classification_code',
        'status',
    ];
}
