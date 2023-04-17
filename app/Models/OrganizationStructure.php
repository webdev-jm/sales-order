<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_title_id',
        'reports_to_id',
        'type'
    ];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function job_title() {
        return $this->belongsTo('App\Models\JobTitle');
    }
}
// k0J13oS4n?!