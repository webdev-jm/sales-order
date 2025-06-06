<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Department extends Model
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
        'department_head_id',
        'department_admin_id',
        'department_code',
        'department_name',
    ];

    public function department_head() {
        return $this->belongsTo('App\Models\User', 'department_head_id')->withTrashed();
    }

    public function department_admin() {
        return $this->belongsTo('App\Models\User', 'department_admin_id')->withTrashed();
    }

    public function users() {
        return $this->hasMany('App\Models\User')->withTrashed();
    }
}
