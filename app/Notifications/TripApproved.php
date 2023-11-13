<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TripApproved extends Notification
{
    use Queueable;

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
        return ['database'];
        // return ['database', 'mail'];
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
            ->line(auth()->user()->fullName().' has approved trip with the code ['.$this->trip->trip_number.'] scheduled for '.date('F j, Y' ,strtotime($this->trip->source == 'activity-plan' ? $this->trip->activity_plan_detail->date : $this->trip->schedule->date)))
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
            'date' => $this->trip->source == 'activity-plan' ? $this->trip->activity_plan_detail->date : $this->trip->schedule->date,
            'module' => 'Trip',
            'status' => 'approved',
            'status_code' => 'success',
            'message' => auth()->user()->fullName().' has approved trip with the code ['.$this->trip->trip_number.'] scheduled for '.date('F j, Y' ,strtotime($this->trip->source == 'activity-plan' ? $this->trip->activity_plan_detail->date : $this->trip->schedule->date)),
            'color' => 'success',
            'url' => url('/trip/'.$this->trip->id)
        ];
    }
}
