<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTerm extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'term_code',
        'description',
        'discount',
        'discount_days',
        'due_days'
    ];
}
