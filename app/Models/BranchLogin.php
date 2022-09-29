<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'operation_process_id',
        'longitude',
        'latitude',
        'accuracy',
        'time_in',
        'time_out',
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function operation_process() {
        return $this->belongsTo('App\Models\OperationProcess');
    }
}
