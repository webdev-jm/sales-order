<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Http\Traits\GlobalTrait;

class ScheduleAddRequest extends Notification
{
    use Queueable;
    use GlobalTrait;

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
            ->subject('Schedule Request')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line($this->schedule->user->fullName().' has created a new schedule on '.$this->schedule->date.' to '.$this->schedule->branch->branch_code.' '.$this->schedule->branch->branch_name.' ')
            ->action('View Details', url('/schedule?user_id='.$this->schedule->user_id))
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
            'status_code' => 'success',
            'message' => $this->schedule->user->fullName().' has created a new schedule on '.$this->schedule->date.' to '.$this->schedule->branch->branch_code.' '.$this->schedule->branch->branch_name.' ',
            'color' => 'success',
            'url' => url('/schedule?user_id='.$this->schedule->user_id)
        ];
    }
}
