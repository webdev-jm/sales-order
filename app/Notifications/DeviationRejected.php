<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Http\Traits\GlobalTrait;

class DeviationRejected extends Notification
{
    use Queueable;
    use GlobalTrait;

    public $deviation;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($deviation)
    {
        $this->afterCommit();
        $this->deviation = $deviation;
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

        // $tableData = [
        //     'columns' => [
        //         'column1',
        //         'column2'
        //     ],
        //     'data' => [
        //         ['Column 1', 'Column 2'],
        //         ['Data1', 'Data2'],
        //         ['Data3', 'Data4'],
        //     ]
        // ];

        return (new MailMessage)
            ->from('notify@bevi.com.ph', 'SMS - Sales Management System')
            ->subject('Deviation Request Rejected')
            ->greeting('Hello! '.$notifiable->fullName())
            ->line('Deviation request has been rejected')
            ->line('Here is table data:')
            // ->with(['tableData' => $tableData])
            ->action('View Details', url('/schedule/deviations'))
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
            'id' => $this->deviation->id,
            'date' => $this->deviation->date,
            'module' => 'Deviation Request',
            'status' => $this->deviation->status,
            'status_code' => 'danger',
            'message' => 'Deviation request has been rejected',
            'color' => 'danger',
            'url' => url('/schedule/deviations')
        ];
    }
}
