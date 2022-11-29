<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyActivityReportRejected extends Notification
{
    use Queueable;

    public $weekly_activity_report;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($weekly_activity_report)
    {
        $this->afterCommit();
        $this->weekly_activity_report = $weekly_activity_report;
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
            ->subject('Weekly Activity Report')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('Weekly activity report has been rejected.')
            ->action('View Details', url('/war/'.$this->weekly_activity_report->id))
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
            'id' => $this->weekly_activity_report->id,
            'date' => $this->weekly_activity_report->date_submitted,
            'module' => 'Weekly Activity Report',
            'status' => $this->weekly_activity_report->status,
            'status_code' => 'danger',
            'message' => 'Weekly activity report has been rejected.',
            'color' => 'danger',
            'url' => url('/war/'.$this->weekly_activity_report->id)
        ];
    }
}
