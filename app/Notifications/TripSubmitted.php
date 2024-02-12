<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Http\Traits\GlobalTrait;

class TripSubmitted extends Notification
{
    use Queueable;
    use GlobalTrait;

    public $trip;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($trip)
    {
        $this->afterCommit();
        $this->trip = $trip;
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
            ->subject('Trip has been submitted')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line(auth()->user()->fullName().' submitted a trip request with the code ['.$this->trip->trip_number.'] scheduled for '.date('F j, Y' ,strtotime($this->trip->departure)).', and it is currenly pending for your approval')
            ->action('View Details', url('/trip/'.$this->trip->id))
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
            'id' => $this->trip->id,
            'date' => $this->trip->departure,
            'module' => 'Trip',
            'status' => 'submitted',
            'status_code' => 'secondary',
            'message' => auth()->user()->fullName().'  has initiated a trip request with the code ['.$this->trip->trip_number.'] scheduled for '.date('F j, Y' ,strtotime($this->trip->departure)).', and it is currenly pending for your approval',
            'color' => 'secondary',
            'url' => url('/trip/'.$this->trip->id)
        ];
    }
}
