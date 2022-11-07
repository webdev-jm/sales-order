<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title'
    ];

    public function job_titles() {
        return $this->hasMany('App\Models\OrganizationStructure');
    }
}
