<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class Paf extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default')); // Default to 'mysql' if not set
    }

    protected $fillable = [
        'account_id',
        'user_id',
        'paf_expense_type_id',
        'paf_support_type_id',
        'paf_activity_id',
        'paf_number',
        'title',
        'concept',
        'start_date',
        'end_date',
        'status',
    ];

    public function paf_details(): HasMany
    {
        return $this->hasMany(PafDetail::class, 'paf_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function expense_type(): BelongsTo
    {
        return $this->belongsTo(PafExpenseType::class, 'paf_expense_type_id');
    }

    public function support_type(): BelongsTo
    {
        return $this->belongsTo(PafSupportType::class, 'paf_support_type_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(PafActivity::class, 'paf_activity_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PafApproval::class);
    }

    public function pre_plan(): HasOne
    {
        return $this->hasOne(PafPrePlan::class);
    }
}

