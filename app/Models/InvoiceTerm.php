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

    public function account() {
        return $this->hasMany('App\Models\Account');
    }

    public function scopeInvoiceTermSearch($query, $search) {
        if($search != '') {
            $invoice_terms = $query->orderBy('id', 'DESC')
            ->where('term_code', 'like', '%'.$search.'%')
            ->orWhere('description', 'like', '%'.$search.'%')
            ->orWhere('discount', 'like', '%'.$search.'%')
            ->orWhere('discount', 'like', '%'.$search.'%')
            ->orWhere('discount_days', 'like', '%'.$search.'%')
            ->orWhere('due_days', 'like', '%'.$search.'%')
            ->paginate(10)->onEachSide(1)->appends(request()->query());
        } else {
            $invoice_terms = $query->orderBy('id', 'DESC')
            ->paginate(10)->onEachSide(1)->appends(request()->query());
        }

        return $invoice_terms;
    }
}
