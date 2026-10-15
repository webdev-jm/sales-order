<?php

namespace App\Services;

use App\Exceptions\SalesOrderCancelRequestException;
use App\Models\SalesOrder;
use App\Models\SalesOrderCancelRequest;
use App\Models\User;
use App\Notifications\SoCancelRequestCompleted;
use App\Notifications\SoCancelRequestInProgress;
use App\Notifications\SoCancelRequestRejected;
use App\Notifications\SoCancelRequestSubmitted;
use App\Notifications\SoCancelRequestWithdrawn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Files and moves on sales order cancel requests, and sends the notifications.
 *
 * A salesman requests the cancelation of an order that is already in SYSPRO, in an account
 * assigned to them. SCM (everyone with the process permission) is notified, marks the request
 * In progress, cancels the order in SYSPRO, then completes the request, which cancels the SMS
 * order, or rejects it with remarks. The requestor is notified at each step and can withdraw
 * the request while it is still Pending.
 */
class SalesOrderCancelRequestService
{
    public const CREATE_PERMISSION  = 'so cancel request create';
    public const PROCESS_PERMISSION = 'so cancel request process';

    /**
     * Why this user cannot request the cancelation of this order, or null when they can.
     */
    public function eligibilityError(SalesOrder $salesOrder, User $user): ?string
    {
        if (!$user->can(self::CREATE_PERMISSION)) {
            return 'You do not have access to request sales order cancelations.';
        }

        $accountId = $salesOrder->account_login?->account_id;
        if (empty($accountId) || !$user->accounts()->whereKey($accountId)->exists()) {
            return 'This order belongs to an account that is not assigned to you.';
        }

        if ($salesOrder->status === 'cancelled') {
            return 'This order is already cancelled.';
        }

        if (!$salesOrder->isInSyspro()) {
            return 'Only orders already in SYSPRO can be cancelled through a request.';
        }

        $open = $salesOrder->open_cancel_request()->first();
        if ($open) {
            return 'Cancel request ' . $open->request_number . ' is already open for this order.';
        }

        return null;
    }

    /**
     * Orders this user can request the cancelation of, matching the search on control number,
     * PO number or SYSPRO SO number.
     */
    public function eligibleOrders(User $user, string $search = ''): Builder
    {
        $accountIds = $user->accounts()->pluck('accounts.id');
        $search     = trim($search);

        return SalesOrder::query()
            ->with('account_login.account')
            ->whereHas('account_login', fn (Builder $query) => $query->whereIn('account_id', $accountIds))
            ->where('upload_status', 1)
            ->whereNotNull('reference')
            ->where('reference', '<>', '')
            ->where('status', '<>', 'cancelled')
            ->whereDoesntHave('cancel_requests', fn (Builder $query) => $query->whereIn('status', SalesOrderCancelRequest::OPEN_STATUSES))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('control_number', 'like', '%' . $search . '%')
                        ->orWhere('po_number', 'like', '%' . $search . '%')
                        ->orWhere('reference', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('id');
    }

    public function submit(SalesOrder $salesOrder, User $user, string $reason): SalesOrderCancelRequest
    {
        $error = $this->eligibilityError($salesOrder, $user);
        if ($error !== null) {
            throw new SalesOrderCancelRequestException($error);
        }

        $request = DB::connection($salesOrder->getConnectionName())->transaction(function () use ($salesOrder, $user, $reason) {
            return SalesOrderCancelRequest::create([
                'request_number' => $this->nextRequestNumber(),
                'sales_order_id' => $salesOrder->id,
                'user_id'        => $user->id,
                'reason'         => trim($reason),
                'status'         => SalesOrderCancelRequest::STATUS_PENDING,
            ]);
        });

        activity('created')
            ->performedOn($request)
            ->log(':causer.firstname :causer.lastname requested to cancel sales order ' . $salesOrder->control_number . ' (:subject.request_number)');

        $this->send($this->processors(), new SoCancelRequestSubmitted($request));

        return $request;
    }

    public function withdraw(SalesOrderCancelRequest $request, User $user): void
    {
        if ((int) $request->user_id !== (int) $user->id) {
            throw new SalesOrderCancelRequestException('Only the salesman who filed this request can withdraw it.');
        }

        $this->ensureStatus($request, SalesOrderCancelRequest::STATUS_PENDING, 'It can only be withdrawn while it is Pending.');

        $request->update([
            'status'    => SalesOrderCancelRequest::STATUS_WITHDRAWN,
            'closed_at' => now(),
        ]);

        activity('withdrawn')
            ->performedOn($request)
            ->log(':causer.firstname :causer.lastname withdrew cancel request :subject.request_number');

        $this->send($this->processors(), new SoCancelRequestWithdrawn($request));
    }

    public function markInProgress(SalesOrderCancelRequest $request, User $user): void
    {
        $this->ensureProcessor($user);
        $this->ensureStatus($request, SalesOrderCancelRequest::STATUS_PENDING, 'Only a Pending request can be marked In progress.');

        $request->update([
            'status'         => SalesOrderCancelRequest::STATUS_IN_PROGRESS,
            'processed_by'   => $user->id,
            'in_progress_at' => now(),
        ]);

        activity('updated')
            ->performedOn($request)
            ->log(':causer.firstname :causer.lastname started processing cancel request :subject.request_number');

        $this->send($request->user, new SoCancelRequestInProgress($request));
    }

    /**
     * SCM has cancelled the order in SYSPRO: close the request and cancel the SMS order.
     */
    public function complete(SalesOrderCancelRequest $request, User $user, ?string $remarks = null): void
    {
        $this->ensureProcessor($user);
        $this->ensureStatus($request, SalesOrderCancelRequest::STATUS_IN_PROGRESS, 'Mark the request In progress before completing it.');

        DB::connection($request->getConnectionName())->transaction(function () use ($request, $user, $remarks) {
            $request->update([
                'status'       => SalesOrderCancelRequest::STATUS_COMPLETED,
                'processed_by' => $user->id,
                'remarks'      => $this->cleanRemarks($remarks),
                'closed_at'    => now(),
            ]);

            $request->sales_order->update(['status' => 'cancelled']);
        });

        activity('approved')
            ->performedOn($request)
            ->log(':causer.firstname :causer.lastname completed cancel request :subject.request_number; sales order ' . $request->sales_order->control_number . ' is cancelled');

        $this->send($request->user, new SoCancelRequestCompleted($request));
    }

    public function reject(SalesOrderCancelRequest $request, User $user, string $remarks): void
    {
        $this->ensureProcessor($user);
        $this->ensureStatus($request, SalesOrderCancelRequest::STATUS_IN_PROGRESS, 'Mark the request In progress before rejecting it.');

        if ($this->cleanRemarks($remarks) === null) {
            throw new SalesOrderCancelRequestException('Enter the reason for rejecting the request.');
        }

        $request->update([
            'status'       => SalesOrderCancelRequest::STATUS_REJECTED,
            'processed_by' => $user->id,
            'remarks'      => $this->cleanRemarks($remarks),
            'closed_at'    => now(),
        ]);

        activity('rejected')
            ->performedOn($request)
            ->log(':causer.firstname :causer.lastname rejected cancel request :subject.request_number');

        $this->send($request->user, new SoCancelRequestRejected($request));
    }

    /**
     * Whether this user may open the request: its requestor, or SCM.
     */
    public function canView(SalesOrderCancelRequest $request, User $user): bool
    {
        return (int) $request->user_id === (int) $user->id || $user->can(self::PROCESS_PERMISSION);
    }

    /**
     * The users who process requests and are notified of new and withdrawn ones.
     *
     * @return Collection<int, User>
     */
    public function processors(): Collection
    {
        return User::permission(self::PROCESS_PERMISSION)->get();
    }

    /**
     * CR-YYYYMM-0001, restarting each month. Call inside a transaction: the row lock keeps two
     * requests filed at the same moment from taking the same number.
     */
    public function nextRequestNumber(): string
    {
        $prefix = 'CR-' . now()->format('Ym') . '-';

        $last = SalesOrderCancelRequest::query()
            ->where('request_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('request_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function ensureProcessor(User $user): void
    {
        if (!$user->can(self::PROCESS_PERMISSION)) {
            throw new SalesOrderCancelRequestException('You do not have access to process cancel requests.');
        }
    }

    private function ensureStatus(SalesOrderCancelRequest $request, string $status, string $hint): void
    {
        if ($request->status !== $status) {
            throw new SalesOrderCancelRequestException('This request is ' . ucfirst($request->status) . '. ' . $hint);
        }
    }

    private function cleanRemarks(?string $remarks): ?string
    {
        $remarks = trim((string) $remarks);

        return $remarks === '' ? null : $remarks;
    }

    /**
     * A failed email must not undo the request, so delivery errors are logged, as the other
     * request modules do.
     *
     * @param  mixed  $notifiables
     */
    private function send($notifiables, BaseNotification $notification): void
    {
        if (empty($notifiables) || ($notifiables instanceof Collection && $notifiables->isEmpty())) {
            return;
        }

        try {
            Notification::send($notifiables, $notification);
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }
    }
}
