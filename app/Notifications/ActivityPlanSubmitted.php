<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Http\Traits\GlobalTrait;

class ActivityPlanSubmitted extends Notification
{
    use Queueable;
    use GlobalTrait;

    public $activity_plan;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($activity_plan)
    {
        $this->afterCommit();
        $this->activity_plan = $activity_plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $setting = $this->getSettings();

        if($setting->email_sending) {
            return ['mail', 'database'];
        } else {
            return ['database'];
        }
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
            ->subject('Activity Plan Submitted for Approval')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('Activity Plan for the month of '.date('F Y', strtotime($this->activity_plan->year.'-'.$this->activity_plan->month.'-01')).' by '.$this->activity_plan->user->fullName().' has been submitted for approval.')
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
            'status_code' => 'primary',
            'message' => 'Activity Plan for the month of '.date('F Y', strtotime($this->activity_plan->year.'-'.$this->activity_plan->month.'-01')).' by '.$this->activity_plan->user->fullName().' has been submitted for approval.',
            'color' => 'primary',
            'url' => url('/mcp/'.$this->activity_plan->id)
        ];
    }
}
