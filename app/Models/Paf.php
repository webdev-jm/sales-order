<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Paf extends Model
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
        'user_id',
        'paf_number',
        'title',
        'concept',
        'expense_type',
        'support_type',
        'start_date',
        'end_date',
        'status',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function approvals() {
        return $this->hasMany('App\Models\PafApproval');
    }

    public function paf_details() {
        return $this->hasMany('App\Models\PafDetail');
    }
}
