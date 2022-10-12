<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountProductReference extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'product_id',
        'account_reference',
        'description',
        'active'
    ];

    public function account() {
        return $this->belongsTo('App\Models\Account');
    }

    public function product() {
        return $this->belongsTo('App\Models\Product');
    }

    public function scopeAccountProductReferenceSearch($query, $search, $limit) {
        if($search != '') {
            $account_product_references = $query->orderBy('id', 'DESC')
            ->whereHas('account', function($qry) use($search) {
                $qry->where('account_code', 'like', '%'.$search.'%')
                ->orWhere('account_name', 'like', '%'.$search.'%')
                ->orWhere('short_name', 'like', '%'.$search.'%');
            })
            ->orWhereHas('product', function($qry) use($search) {
                $qry->where('stock_code', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('size', 'like', '%'.$search.'%')
                ->orWhere('bar_code', 'like', '%'.$search.'%');
            })
            ->orWhere('account_reference', 'like', '%'.$search.'%')
            ->orWhere('description', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $account_product_references = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $account_product_references;
    }
}
