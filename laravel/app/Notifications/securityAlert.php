<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class securityAlert extends Notification
{
    use Queueable;

    protected $user;
    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast','mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetTime = now();

        return (new MailMessage)
            ->subject('Security Alert: Password Changed')
            ->greeting('Dear ' . $this->user->name . ',')
            ->action("Contact Admin",url('/'))
            ->line('Your account password was recently changed at ' . $resetTime . '. If you did not perform this action, please contact support immediately to secure your account.');
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'message' => 'Dear ' . $this->user->name . ', your account password was recently changed. If you did not perform this action, please contact support immediately to secure your account.',
            'user_name' => $this->user->name,
            'title' => 'Security Alert',
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
            'message' => 'Dear ' . $this->user->name . ', your account password was recently changed. If you did not perform this action, please contact support immediately to secure your account.',
            'user_name' => $this->user->name,
            'title' => 'Security Alert',
        ];
    }
}
