<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'operation_process_id',
        'number',
        'description',
        'remarks',
    ];

    public function operation_process() {
        return $this->belongsTo('App\Models\OperationProcess');
    }
    
}