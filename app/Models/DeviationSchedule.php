<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'deviation_id',
        'branch_id',
        'date',
        'activity',
        'type'
    ];

    public function deviation() {
        return $this->belongsTo('App\Models\Deviation');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }
}
