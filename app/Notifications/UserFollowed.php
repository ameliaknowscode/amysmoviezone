<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserFollowed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly User $follower) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("@{$this->follower->username} started following you on " . config('app.name'))
            ->greeting("Hi {$notifiable->name}!")
            ->line("**@{$this->follower->username}** ({$this->follower->name}) started following you.")
            ->action('View their profile', route('profile.show', $this->follower->username))
            ->line('You can manage your email notification preferences in your profile settings.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'              => 'user_followed',
            'follower_id'       => $this->follower->id,
            'follower_name'     => $this->follower->name,
            'follower_username' => $this->follower->username,
        ];
    }
}
