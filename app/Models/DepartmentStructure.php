<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentStructure extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'department_id',
        'user_id',
        'reports_to_ids',
        'designation',
    ];

    public function department() {
        return $this->belongsTo('App\Models\Department');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }
}
