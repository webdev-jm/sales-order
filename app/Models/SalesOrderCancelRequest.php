<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

/**
 * A salesman's request to cancel a sales order that is already in SYSPRO. SCM cancels the
 * order in SYSPRO, then completes the request here, which cancels the SMS order too.
 *
 * Pending -> In progress -> Completed or Rejected. The requestor can withdraw it while Pending.
 */
class SalesOrderCancelRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_REJECTED    = 'rejected';
    public const STATUS_WITHDRAWN   = 'withdrawn';

    /** Statuses in which a request still waits on someone. */
    public const OPEN_STATUSES = [self::STATUS_PENDING, self::STATUS_IN_PROGRESS];

    /** @var array<string, string> status => AdminLTE badge colour */
    public const STATUS_COLORS = [
        self::STATUS_PENDING     => 'secondary',
        self::STATUS_IN_PROGRESS => 'info',
        self::STATUS_COMPLETED   => 'success',
        self::STATUS_REJECTED    => 'danger',
        self::STATUS_WITHDRAWN   => 'light',
    ];

    protected $fillable = [
        'request_number',
        'sales_order_id',
        'user_id',
        'reason',
        'status',
        'processed_by',
        'remarks',
        'in_progress_at',
        'closed_at',
    ];

    protected $casts = [
        'in_progress_at' => 'datetime',
        'closed_at'      => 'datetime',
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
        return $this->belongsTo(SalesOrder::class)->withTrashed();
    }

    /** The salesman who filed the request. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /** The SCM user who last moved the request on. */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by')->withTrashed();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }
}
