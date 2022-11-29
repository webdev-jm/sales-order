<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviationApproved extends Notification
{
    use Queueable;

    public $deviation;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($deviation)
    {
        $this->afterCommit();
        $this->deviation = $deviation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from('notify@bevi.com.ph', 'SMS - Sales Management System')
            ->subject('Deviation Request Approved')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('Deviation request has been approved')
            ->action('View Details', url('/schedule/deviations'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->deviation->id,
            'date' => $this->deviation->date,
            'module' => 'Deviation Request',
            'status' => $this->deviation->status,
            'status_code' => 'success',
            'message' => 'Deviation request has been approved',
            'color' => 'success',
            'url' => url('/schedule/deviations')
        ];
    }
}
