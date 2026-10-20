<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

/**
 * One AI provider as set on the AI Settings page: whether it is used, its place in the failover
 * order, the model, how hard it thinks and its token prices. Once any row exists, these rows
 * replace AI_PROVIDER, AI_FAILOVER and the model settings in the .env file; API keys always stay
 * in the .env file.
 */
class AiProviderSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_enabled',
        'priority',
        'model',
        'effort',
        'base_url',
        'input_price',
        'output_price',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled'   => 'boolean',
        'priority'     => 'integer',
        'input_price'  => 'float',
        'output_price' => 'float',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default'));
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Estimated cost in US dollars of a call with these token counts, or null when no price is set. Cached input
     * tokens are part of the input tokens; they are billed at the provider's cache rates (config ai.providers.*.cache_*_price).
     */
    public function costOf(int $inputTokens, int $outputTokens, int $cachedInputTokens = 0, int $cacheWriteTokens = 0): ?float
    {
        if ($this->input_price === null && $this->output_price === null) {
            return null;
        }

        $inputPrice = (float) $this->input_price;
        $readRate = (float) config("ai.providers.{$this->name}.cache_read_price", 1.0);
        $writeRate = (float) config("ai.providers.{$this->name}.cache_write_price", 1.0);
        $fresh = max(0, $inputTokens - $cachedInputTokens - $cacheWriteTokens);

        $input = $fresh * $inputPrice + $cachedInputTokens * $inputPrice * $readRate + $cacheWriteTokens * $inputPrice * $writeRate;

        return ($input + $outputTokens * (float) $this->output_price) / 1_000_000;
    }
}
