<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationProcess extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'operation_process'
    ];

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function activities() {
        return $this->hasMany('App\Models\Activity');
    }
}