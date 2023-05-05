<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityPlanApproved extends Notification
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
            ->subject('Activity Plan has been approved')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('Activity Plan for the month of '.date('F Y', strtotime($this->activity_plan->year.'-'.$this->activity_plan->month.'-01')).' has been approved by '.$this->approval->user->fullName())
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
            'status_code' => 'success',
            'message' => 'Activity Plan for the month of '.date('F Y', strtotime($this->activity_plan->year.'-'.$this->activity_plan->month.'-01')).' by '.$this->activity_plan->user->fullName().' has been approved.',
            'color' => 'success',
            'url' => url('/mcp/'.$this->activity_plan->id)
        ];
    }
}
