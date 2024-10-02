<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PafPrePlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'paf_id',
        'account_id',
        'paf_support_type_id',
        'paf_activity_id',
        'pre_plan_number',
        'year',
        'start_date',
        'end_date',
        'title',
        'concept'
    ];

    public function pre_plan_details() {
        return $this->hasMany('App\Models\PafPrePlanDetail');
    }
     
    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function support_type() {
        return $this->belongsTo('App\Models\PafSupportType', 'paf_support_type_id', 'id');
    }
}
