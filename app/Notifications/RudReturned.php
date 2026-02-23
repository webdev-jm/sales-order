<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Http\Traits\GlobalTrait;

class RudReturned extends Notification
{
    use Queueable;
     use GlobalTrait;

    public $rud, $approval;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($rud, $approval)
    {
        $this->rud = $rud;
        $this->approval = $approval;
        $this->afterCommit();
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
            ->subject('RUD has been returned to you.')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('RUD has been returned to you by '.$this->approval->user->fullName())
            ->action('View Details', url('/credit-memo/'.$this->rud->id))
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
            'id' => $this->rud->id,
            'date' => $this->rud->year.'-'.$this->rud->month,
            'module' => 'RUD',
            'status' => $this->rud->status,
            'status_code' => 'warning',
            'message' => 'RUD has been returned to you.',
            'color' => 'warning',
            'url' => url('/credit-memo/'.$this->rud->id)
        ];
    }
}
