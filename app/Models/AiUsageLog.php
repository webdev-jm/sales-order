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

    /** Answered from a saved scan of the same file, so no tokens were used. */
    public const STATUS_CACHED = 'cached';

    public const FEATURE_PO_SCAN = 'po_scan';

    public const FEATURE_ASSISTANT = 'sales_assistant';

    public const FEATURE_INSIGHTS = 'report_insights';

    public const FEATURE_MAPPING = 'mapping_help';

    /** @var array<string, string> Names shown on the AI Usage page */
    public const FEATURES = [
        self::FEATURE_PO_SCAN   => 'PO scan',
        self::FEATURE_ASSISTANT => 'Sales assistant',
        self::FEATURE_INSIGHTS  => 'Report insights',
        self::FEATURE_MAPPING   => 'Upload mapping help',
    ];

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
        'estimated_cost',
        'status',
        'error',
        'duration_ms',
    ];

    protected $casts = [
        'input_tokens'   => 'integer',
        'output_tokens'  => 'integer',
        'total_tokens'   => 'integer',
        'estimated_cost' => 'float',
        'duration_ms'    => 'integer',
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
