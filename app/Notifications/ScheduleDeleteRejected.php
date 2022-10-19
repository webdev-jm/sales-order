<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleDeleteRejected extends Notification
{
    use Queueable;

    public $schedule;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($schedule)
    {
        $this->afterCommit();
        $this->schedule = $schedule;
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
            ->subject('Schedule Delete Request Rejected')
            ->greeting('Hello!')
            ->line('Schedule Delete request for '.$this->schedule->branch->branch_code.' '.$this->schedule->branch->branch_name.' on '.$this->schedule->date.' by '.$this->schedule->user->firstname.' '.$this->schedule->user->lastname.' has been rejected.')
            ->action('View Details', url('/schedule/list'))
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
            'id' => $this->schedule->id,
            'date' => $this->schedule->date,
            'module' => 'Schedule Request',
            'status' => $this->schedule->status,
            'status_code' => 'maroon',
            'message' => 'Schedule Delete request for '.$this->schedule->branch->branch_code.' '.$this->schedule->branch->branch_name.' on '.$this->schedule->date.' by '.$this->schedule->user->firstname.' '.$this->schedule->user->lastname.' has been rejected.',
            'color' => 'maroon',
            'url' => url('/schedule/list')
        ];
    }
}
