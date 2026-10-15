<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

/**
 * The customer's PO document kept with a sales order: a file the user attached, or the PDF
 * the order was scanned from.
 */
class SalesOrderAttachment extends Model
{
    public const SOURCE_UPLOAD = 'upload';
    public const SOURCE_SCAN   = 'scan';

    protected $fillable = [
        'sales_order_id',
        'user_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'source',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', config('database.default'));
    }

    public function sales_order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * File size for display, e.g. "1.2 MB".
     */
    public function readableSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
