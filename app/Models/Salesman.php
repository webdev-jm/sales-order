<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salesman extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'code',
    ];

    public function salesmen_locations() {
        return $this->hasMany('App\Models\SalesmenLocation');
    }

    public function productivity_report_data() {
        return $this->hasMany('App\Models\ProductivityReportData');
    }

    public function scopeSalesmanAjax($query, $search) {

        $salesmen = $query->select('id', 'code', 'name')
            ->when($search == '', function($qry) use($search) {
                $qry->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            })
            ->limit(5)->get();

        $response = [];
        foreach($salesmen as $salesman) {
            $response[] = array(
                'id' => $salesman->id,
                'text' => $salesman->code.' '.$salesman->name
            );
        }

        return $response;
    }
}
