<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GreetingApprovedClient extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Our Hotel!')
            ->greeting("Hello {$this->user->name},")
            ->line('Congratulations! Your account has been approved.')
            ->line('You can now book rooms and enjoy our services.')
            ->action('Visit Our Website', url('/'))
            ->line('Thank you for choosing our hotel!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Congratulations {$this->user->name}! Your account has been approved.",
        ];
    }

    //     public function approveUser($id)
    // {
    //     // Find the user by ID
    //     $user = User::findOrFail($id);

    //     // Update the user's approval status
    //     $user->is_approved = true;
    //     $user->save();

    //     // Send the notification (Queued!)
    //     $user->notify(new GreetingApprovedClient($user));

    //     return response()->json(['message' => 'User approved and notified successfully!']);
    // }

    // public function approveUser($userId)
    // {
    //     $user = User::findOrFail($userId);

    //     if (!$user->is_approved) {
    //         $user->is_approved = true;
    //         $user->save();

    //         // Send Notification (Queued & Stored in Database)
    //         $user->notify(new WelcomeEmailNotification($user));

    //         return response()->json(['message' => 'User approved. Welcome email sent and notification stored.']);
    //     }

    //     return response()->json(['message' => 'User is already approved.']);
    // }
}
