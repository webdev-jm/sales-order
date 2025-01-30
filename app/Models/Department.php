<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;

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
