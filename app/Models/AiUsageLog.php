<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

/**
 * One call to an AI provider and the tokens it used. Failed calls are logged too, because a call
 * whose reply could not be read has still been billed.
 */
class AiUsageLog extends Model
{
    use HasFactory;

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const FEATURE_PO_SCAN = 'po_scan';

    protected $fillable = [
        'user_id',
        'account_id',
        'feature',
        'provider',
        'model',
        'reference',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'status',
        'error',
        'duration_ms',
    ];

    protected $casts = [
        'input_tokens'  => 'integer',
        'output_tokens' => 'integer',
        'total_tokens'  => 'integer',
        'duration_ms'   => 'integer',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
