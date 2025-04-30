<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    protected $order;

    // Constructor to pass the order data to the notification
    public function __construct($order)
    {
        $this->order = $order;
    }

    // Define which channels the notification will be sent on (in this case, database)
    public function via($notifiable)
    {
        return ['database'];
    }

    // Data for the database notification
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your order #'.$this->order->id.' has been successfully placed!',
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'total_price' => $this->order->total_price,
        ];
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
