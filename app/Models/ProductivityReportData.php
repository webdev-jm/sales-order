<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductivityReportData extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'productivity_report_id',
        'branch_id',
        'classification_id',
        'date',
        'salesman',
        'visited',
        'sales',
    ];

    public function productivity_report() {
        return $this->belongsTo('App\Models\ProductivityReport');
    }

    public function branch() {
        return $this->belongsTo('App\Models\Branch');
    }

    public function classification() {
        return $this->belongsTo('App\Models\Classification');
    }
}
