<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'name',
        'order_limit'
    ];

    public function price_codes() {
        return $this->hasMany('App\Models\PriceCode');
    }

    public function cost_centers() {
        return $this->hasMany('App\Models\CostCenter');
    }

    public function scopeCompanySearch($query, $search, $limit) {
        if($search != '') {
            $companies = $query->orderBy('id', 'DESC')
            ->where('name', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $companies = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $companies;
    }
}
