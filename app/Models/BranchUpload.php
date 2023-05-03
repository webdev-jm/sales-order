<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'region',
        'classification',
        'branch_name',
        'branch_code',
        'account_group',
        'inventory',
        'type',
        'area_code',
        'area_name',
        'classification_code',
        'status',
    ];
}
