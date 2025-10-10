<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class PPUForm extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table='ppuforms';


    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'account_login_id',
        'control_number',
        'status',
        'date_prepared',
        'date_submitted',
        'pickup_date',
        'status',
        'total_quantity',
        'total_amount',
    ];

    public function account_login() {
        return $this->belongsTo('App\Models\AccountLogin');
    }

    public function ppuform_item() {
        return $this->hasMany('App\Models\PPUFormItem');
    }
}
