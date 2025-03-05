<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Classification extends Model
{
    use HasFactory;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'classification_name',
        'classification_code'
    ];

    public function branches() {
        return $this->hasMany('App\Models\Branch');
    }

    public function scopeClassificationSearch($query, $search, $limit) {
        if($search != '') {
            $classifications = $query->orderBy('id', 'DESC')
            ->where('classification_name', 'like', '%'.$search.'%')
            ->orWhere('classification_code', 'like', '%'.$search.'%')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $classifications = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $classifications;
    }
}
