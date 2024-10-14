<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Http\Traits\GlobalTrait;

class PafApproved extends Notification
{
    use Queueable;
    use GlobalTrait;

    public $paf;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($paf)
    {
        $this->afterCommit();
        $this->paf = $paf;
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
            ->subject('PAF has been approved')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line(auth()->user()->fullName().' approved a paf request with the code ['.$this->paf->paf_number.'], and it is currenly pending for your approval')
            ->action('View Details', url('/paf/'.$this->paf->id))
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
            'id' => $this->paf->id,
            'date' => $this->paf->start_date,
            'module' => 'Paf',
            'status' => 'submitted',
            'status_code' => 'info',
            'message' => auth()->user()->fullName().' approved a paf request with the code ['.$this->paf->paf_number.'], and it is currenly pending for your approval',
            'color' => 'info',
            'url' => url('/paf/'.$this->paf->id)
        ];
    }
}
