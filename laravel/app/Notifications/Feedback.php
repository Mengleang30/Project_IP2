<?php

namespace App\Notifications;

use Google\Service\CloudSearch\UserId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Feedback extends Notification
{
    use Queueable;

    protected $bookId;
    protected $userId;
    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($bookId,$userId,$comment)
    {
        $this->bookId = $bookId;
        $this->userId= $userId;
        $this->comment= $comment;
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
            'title' => 'New Comment From Customer',
            'message' => "User ID: {$this->userId} added a new comment on Book ID {$this->bookId}.",
            'book_id' => $this->bookId,
            // 'user_id' => $this->userId,
            // 'comment'=> $this->comment,
        ];
    }
}
