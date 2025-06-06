<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class ChannelOperation extends Model
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
        'branch_login_id',
        'date',
        'store_in_charge',
        'total_findings',
        'position',
        'status',
    ];

    public function branch_login() {
        return $this->belongsTo('App\Models\BranchLogin');
    }

    public function trade_displays() {
        return $this->hasMany('App\Models\ChannelOperationTradeDisplay');
    }

    public function extra_displays() {
        return $this->hasMany('App\Models\ChannelOperationExtraDisplay');
    }

    public function competetive_reports() {
        return $this->hasMany('App\Models\ChannelOperationCompetetiveReport');
    }

    public function merch_updates() {
        return $this->hasMany('App\Models\ChannelOperationMerchUpdate');
    }

    public function display_rentals() {
        return $this->hasMany('App\Models\ChannelOperationDisplayRental');
    }

    public function trade_marketing_activities() {
        return $this->hasMany('App\Models\ChannelOperationTradeMarketingActivity');
    }
}
