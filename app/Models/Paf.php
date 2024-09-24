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
        return $this->belongsTo('App\Models\User');
    }

    public function expense_type() {
        return $this->belongsTo('App\Models\PafExpenseType');
    }

    public function support_type() {
        return $this->belongsTo('App\Models\PafSupportType');
    }

    public function activity() {
        return $this->belongsTo('App\Models\PafActivity');
    }

    public function approvals() {
        return $this->hasMany('App\Models\PafApproval');
    }
}
