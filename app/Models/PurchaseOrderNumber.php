<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'po_number',
    ];
}
