<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesmenLocation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'salesman_id',
        'province',
        'city',
    ];

    public function salesman() {
        return $this->belongsTo('App\Models\Salesman');
    }

    public function productivity_report_data() {
        return $this->hasMany('App\Models\ProductivityReportData');
    }
}
