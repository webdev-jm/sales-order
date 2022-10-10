<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'region_id',
        'classification_id',
        'area_id',
        'branch_code',
        'branch_name',
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function users() {
        return $this->belongsToMany('App\Models\User');
    }

    public function region() {
        return $this->belongsTo('App\Models\Region');
    }

    public function classification() {
        return $this->belongsTo('App\Models\Classification');
    }

    public function area() {
        return $this->belongsTo('App\Models\Area');
    }
}
