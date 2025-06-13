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
   protected $payment;
   protected $user;

    public function __construct($order, $payment,$user)
    {
        $this->order = $order;
        $this->payment = $payment;
        $this->user = $user;
    }



    public function via(object $notifiable): array
    {
        return ['database', 'mail','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment was successful! ')
            ->greeting('Hello ' . $this->user . ' !')
            ->line("Order_ID:  {$this->order->id} ")
            ->line("Date:  " . $this->payment->created_at->format('d-m-Y H:i:s') . " ")
            ->line("Payment_Method:  {$this->payment->payment_method} ")
            ->line("Transaction_ID:  {$this->payment->transaction_id} ")
            ->action('View Your Order', url('http://localhost:5173/history'))
            ->line('Thank you for your purchase.!')
            ->salutation('Best regards, Books Store');
    }

    public function toDatabases(){
        return [
            'order_id' => $this->order->id,
            'transaction_Id' => $this->payment->transaction_id,
            'amount' => $this->order->final_price ?? $this->order->total_price,
            'message' => 'Your payment was successful!
             Thank you for your purchase.',
        ];
    }

    public function toBroadcast()
    {
        return [
            'order_id' => $this->order->id,
            'transaction_Id' => $this->payment->transaction_id,
            'title' => 'Payment Success',
            'amount' => $this->order->final_price ?? $this->order->total_price,
            'toCustomer' => $this->order->user->name,
            'message' => ' Dear ! ' . $this->order->user->name .' Your payment was successful! Thank you for your purchase. ',
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
            'transaction_Id' => $this->payment->transaction_id,
            'title' => 'Payment Success',
            'amount' => $this->order->final_price ?? $this->order->total_price,
            'toCustomer' => $this->order->user->name,
            'message' => ' Dear ! ' . $this->order->user->name .' Your payment was successful! Thank you for your purchase. ',
        ];
    }
}
