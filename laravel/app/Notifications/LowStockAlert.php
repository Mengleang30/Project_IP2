<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */


    protected $book;

    public function __construct($book)
    {
        $this->book = $book;
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database' ,'broadcast'];
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Low Stock Alert',
            'message' => 'The stock for one of your books is low.',
            'book_id' => $this->book->id,
        ];
    }

    public function toBroadcast(object $notifiable)
    {
        return [
            'title' => 'Low Stock Alert',
            'message' => 'The stock for one of your books is low.',
            'book_id' => $this->book->id,
        ];
    }
}
