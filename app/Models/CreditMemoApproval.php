<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class CreditMemoApproval extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'credit_memo_id',
        'user_id',
        'status',
        'remarks',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default')); // Default to 'mysql' if not set
    }

    public function credit_memo() {
        return $this->belongsTo('App\Models\CreditMemo', 'credit_memo_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}

