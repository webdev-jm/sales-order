<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function getConnectionName()
    {
        return 'mysql';
    }

    protected $fillable = [
        'account_id',
        'region_id',
        'classification_id',
        'area_id',
        'branch_code',
        'branch_name',
        'province',
        'city',
        'barangay',
        'address'
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

    public function schedules() {
        return $this->hasMany('App\Models\UserBranchSchedule');
    }

    public function territories() {
        return $this->belongsToMany('App\Models\Territory');
    }

    public function scopeBranchAjax($query, $search) {
        if($search == '') {
            $branches = $query->orderBy('branch_code', 'ASC')
            ->select('id', 'branch_code', 'branch_name')
            ->limit(5)->get();
        } else {
            $branches = $query->orderBy('branch_code', 'ASC')
            ->select('id', 'branch_code', 'branch_name')
            ->where('branch_code', 'like', '%'.$search.'%')
            ->orWhere('branch_name', 'like', '%'.$search.'%')
            ->orWhereHas('account', function($qry) use($search) {
                $qry->where('account_code', 'like', '%'.$search.'%')
                ->orWhere('account_name', 'like', '%'.$search.'%')
                ->orWhere('short_name', 'like', '%'.$search.'%');
            })
            ->limit(5)->get();
        }

        $response = [];
        foreach($branches as $branch) {
            $response[] = [
                'id' => $branch->id,
                'text' => '['.$branch->branch_code.'] '.$branch->branch_name
            ];
        }

        return $response;
    }

    public function scopeBranchSearch($query, $search, $limit) {
        if($search != '') {
            $branches = $query->orderBy('id', 'DESC')
            ->where(function($qry) use ($search) {
                $qry->where('branch_code', 'like', '%'.$search.'%')
                ->orWhere('branch_name', 'like', '%'.$search.'%');
            })
            ->orWhereHas('account', function($qry) use($search) {
                $qry->where('account_code', 'like', '%'.$search.'%')
                ->orWhere('account_name', 'like', '%'.$search.'%')
                ->orWhere('short_name', 'like', '%'.$search.'%');
            })
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $branches = $query->orderBy('id', 'DESC')
            ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $branches;
    }

    public function scopeRestrictedBranchSearch($query, $search, $limit) {
        if($search != '') {
            $branches = $query->orderBy('id', 'DESC')
                ->where(function($qry) use ($search) {
                    $qry->where('branch_code', 'like', '%'.$search.'%')
                        ->orWhere('branch_name', 'like', '%'.$search.'%');

                    $qry->orWhereHas('account', function($qry1) use($search) {
                        $qry1->where('account_code', 'like', '%'.$search.'%')
                            ->orWhere('account_name', 'like', '%'.$search.'%')
                            ->orWhere('short_name', 'like', '%'.$search.'%');
                    });
                })
                ->whereHas('account', function($qry) use($search) {
                    $qry->whereHas('users', function($qry1) {
                        $qry1->where('id', auth()->user()->id);
                    });
                })
                ->paginate($limit)->onEachSide(1)->appends(request()->query());
        } else {
            $branches = $query->orderBy('id', 'DESC')
                ->whereHas('account', function($qry) {
                    $qry->whereHas('users', function($qry1) {
                        $qry1->where('id', auth()->user()->id);
                    });
                })
                ->paginate($limit)->onEachSide(1)->appends(request()->query());
        }

        return $branches;
    }
}
