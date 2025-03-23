<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('We Miss You!')
            ->greeting("Hello {$this->user->name},")
            ->line('We noticed you haven’t logged in for over a month.')
            ->line('We miss having you around! Come back and check out what’s new.')
            ->action('Visit Us', url('/'))
            ->line('Looking forward to seeing you again!');
    }
}
