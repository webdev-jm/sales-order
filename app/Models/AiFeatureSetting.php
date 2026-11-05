<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

/**
 * The providers and models one AI function uses, in the order they are tried, as set on the AI
 * Settings page. A function without a row follows the provider order at the top of that page.
 * A blank model or price means the provider's own.
 *
 * @property array<int, array{name: string, model: string|null, input_price: float|null, output_price: float|null}> $providers
 */
class AiFeatureSetting extends Model
{
    use HasFactory;

    /**
     * What each function does, shown on the AI Settings page. Functions marked reads_pdf send PDF
     * files, so only providers that read PDFs can be picked for them.
     *
     * @var array<string, array{description: string, reads_pdf: bool}>
     */
    public const FEATURES = [
        AiUsageLog::FEATURE_PO_SCAN => [
            'description' => 'Reads account PO PDFs on SO Templates and when a PO PDF is uploaded as a sales order (one or many).',
            'reads_pdf'   => true,
        ],
        AiUsageLog::FEATURE_ASSISTANT => [
            'description' => 'Answers questions about sales on the AI Assistant page, using the sales figures it looks up.',
            'reads_pdf'   => false,
        ],
        AiUsageLog::FEATURE_INSIGHTS => [
            'description' => 'Writes the short insights on the SO Dashboard and the SO report.',
            'reads_pdf'   => false,
        ],
        AiUsageLog::FEATURE_MAPPING => [
            'description' => 'Suggests the products and stores for unmatched lines on the SO Templates review.',
            'reads_pdf'   => false,
        ],
    ];

    protected $fillable = [
        'feature',
        'providers',
        'updated_by',
    ];

    protected $casts = [
        'providers' => 'array',
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

    public static function readsPdf(string $feature): bool
    {
        return self::FEATURES[$feature]['reads_pdf'] ?? false;
    }

    /**
     * The step for this provider and model, when the function lists it. Providers can answer under a newer
     * version of the model asked for (Gemini does), so without an exact match the first step for the provider is used.
     *
     * @return array{name: string, model: string|null, input_price: float|null, output_price: float|null}|null
     */
    public function stepFor(string $provider, string $model): ?array
    {
        $steps = array_values(array_filter((array) $this->providers, fn (array $step): bool => ($step['name'] ?? null) === $provider));

        foreach ($steps as $step) {
            if (($step['model'] ?? null) === $model) {
                return $step;
            }
        }

        return $steps[0] ?? null;
    }
}
