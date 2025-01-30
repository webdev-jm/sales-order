<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paf extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'user_id',
        'paf_expense_type_id',
        'paf_support_type_id',
        'paf_activity_id',
        'paf_number',
        'title',
        'start_date',
        'end_date',
        'concept',
        'status',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function user() {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function expense_type() {
        return $this->belongsTo('App\Models\PafExpenseType', 'paf_expense_type_id', 'id');
    }

    public function support_type() {
        return $this->belongsTo('App\Models\PafSupportType', 'paf_support_type_id', 'id');
    }

    public function activity() {
        return $this->belongsTo('App\Models\PafActivity', 'paf_activity_id', 'id');
    }

    public function approvals() {
        return $this->hasMany('App\Models\PafApproval');
    }

    public function paf_details() {
        return $this->hasMany('App\Models\PafDetail');
    }

    public function pre_plan() {
        return $this->hasOne('App\Models\PafPrePlan', 'paf_id', 'id');
    }
}
