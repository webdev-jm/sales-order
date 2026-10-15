<?php

namespace App\Notifications;

use App\Http\Traits\GlobalTrait;
use App\Models\SalesOrderCancelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Shared shape of the sales order cancel request notifications: the bell always, and email when
 * Settings has email sending on.
 */
abstract class SoCancelRequestNotification extends Notification
{
    use Queueable;
    use GlobalTrait;

    public function __construct(public SalesOrderCancelRequest $cancelRequest)
    {
        $this->afterCommit();
    }

    abstract protected function subject(): string;

    abstract protected function message(): string;

    /** AdminLTE colour of the bell entry. */
    abstract protected function color(): string;

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        $setting = $this->getSettings();

        if ($setting && $setting->email_sending) {
            return ['mail', 'database'];
        }

        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->from('notify@bevi.com.ph', 'SMS - Sales Management System')
            ->subject($this->subject())
            ->greeting('Hello! ' . $notifiable->fullName())
            ->line($this->message())
            ->action('View Request', $this->url())
            ->line('Thank you for using our application!');
    }

    /**
     * @return array{id: int, date: string, module: string, status: string, status_code: string, message: string, color: string, url: string}
     */
    public function toArray($notifiable): array
    {
        return [
            'id'          => $this->cancelRequest->id,
            'date'        => now()->toDateString(),
            'module'      => 'SO Cancel Request',
            'status'      => $this->cancelRequest->status,
            'status_code' => $this->color(),
            'message'     => $this->message(),
            'color'       => $this->color(),
            'url'         => $this->url(),
        ];
    }

    protected function url(): string
    {
        return route('so-cancel-request.show', $this->cancelRequest->id);
    }

    /** "SO 1000123 (PO 4500-77, SYSPRO 245001)" */
    protected function orderLabel(): string
    {
        $order = $this->cancelRequest->sales_order;

        return 'SO ' . $order->control_number . ' (PO ' . $order->po_number . ', SYSPRO ' . $order->reference . ')';
    }

    protected function requestorName(): string
    {
        return $this->cancelRequest->user?->fullName() ?? 'A salesman';
    }
}
