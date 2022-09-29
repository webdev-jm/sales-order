<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchLoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_login_id',
        'activity_id',
        'remarks',
    ];

    public function branch_login() {
        return $this->belongsTo('App\Models\BranchLogin');
    }

    public function activity() {
        return $this->belongsTo('App\Models\Activity');
    }
}
