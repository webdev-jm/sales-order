<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Session;

class AccountUploadTemplate extends Model
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
        'account_id',
        'upload_template_id',
        'type',
        'start_row',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function upload_template() {
        return $this->belongsTo('App\Models\UploadTemplate');
    }
}
