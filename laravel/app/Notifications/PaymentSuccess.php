<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccess extends Notification
{
    use Queueable;

   protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }



    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    public function toDatabases(){
        return [
            'order_id' => $this->order->id,
            'amount' => $this->order->final_price,
            'message' => 'Your payment was successful!
             Thank you for your purchase.',
        ];
    }

    public function toBroadcast()
    {
        return [
            'order_id' => $this->order->id,
            'title' => 'Payment Success',
            'amount' => $this->order->final_price,
            'toCustomer' => $this->order->user->name,
            'message' => ' Dear ! Customer,  Your payment was successful! Thank you for your purchase. ',
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title' => 'Payment Success',
            'amount' => $this->order->final_price,
            'toCustomer' => $this->order->user->name,
            'message' => ' Dear ! Customer,  Your payment was successful! Thank you for your purchase.',
        ];
    }
}
