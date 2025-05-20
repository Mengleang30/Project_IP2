<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $order;
    public function __construct($order)
    {
        $this->order = $order;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
          ->subject('Payment Failed for Your Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We noticed that your payment for order #' . $this->order->id . ' has failed.')
            ->line('Please try again or contact support if you need assistance.')
            ->action('Retry Payment', url('/orders/' . $this->order->id))
            ->line('Thank you for shopping with us!');
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'message' => 'Payment failed for order #' . $this->order->id,
            'order_id' => $this->order->id,
            'status' => 'failed',
            'total_price' => $this->order->total_price,
            'created_at' => now(),
        ];
    }
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Payment failed for order #' . $this->order->id,
            'order_id' => $this->order->id,
            'status' => 'failed',
            'total_price' => $this->order->total_price,
            'created_at' => now(),
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
            'message' => 'Payment failed for order #' . $this->order->id,
            'order_id' => $this->order->id,
            'status' => 'failed',
            'total_price' => $this->order->total_price,
            'created_at' => now(),
        ];
    }
}
