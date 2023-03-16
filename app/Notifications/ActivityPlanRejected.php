<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityPlanRejected extends Notification
{
    use Queueable;

    public $activity_plan, $approval;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($activity_plan, $approval)
    {
        $this->afterCommit();
        $this->activity_plan = $activity_plan;
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
        // return ['database'];
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
            ->subject('Activity Plan has been rejected')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('Activity Plan of '.$this->activity_plan->user->fullName().' for the month of '.date('F Y', strtotime($this->activity_plan->year.'-'.$this->activity_plan->month.'-01')).' has been rejected by '.$this->approval->user->fullName())
            ->action('View Details', url('/mcp/'.$this->activity_plan->id))
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
            'id' => $this->activity_plan->id,
            'date' => $this->activity_plan->year.'-'.$this->activity_plan->month,
            'module' => 'Activity Plan',
            'status' => $this->activity_plan->status,
            'status_code' => 'danger',
            'message' => 'Activity Plan for the month of '.date('F Y', strtotime($this->activity_plan->year.'-'.$this->activity_plan->month.'-01')).' by '.$this->activity_plan->user->fullName().' has been rejected.',
            'color' => 'danger',
            'url' => url('/mcp/'.$this->activity_plan->id)
        ];
    }
}
