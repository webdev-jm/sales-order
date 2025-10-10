<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class PPUFormItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table='ppuform_items';


    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'ppuform_id',
        'rtv_number',
        'rtv_date',
        'branch_name',
        'total_quantity',
        'total_amount',
        'remarks',

    ];

    public function ppuform() {
        return $this->belongsTo('App\Models\PPUForm');
    }


}
