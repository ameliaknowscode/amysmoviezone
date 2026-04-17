<?php

namespace App\Notifications;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharedLog extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User  $logger,
        public readonly Movie $movie,
        public readonly bool  $sameNight = false,
    ) {}

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
        if ($this->sameNight) {
            return (new MailMessage)
                ->subject("You and @{$this->logger->username} watched {$this->movie->title} on the same night!")
                ->greeting("Hi {$notifiable->name}!")
                ->line("You and **@{$this->logger->username}** both watched *{$this->movie->title}* on the same night!")
                ->action('See the film', $this->movie->publicUrl())
                ->line('You can manage your email notification preferences in your profile settings.');
        }

        return (new MailMessage)
            ->subject("@{$this->logger->username} watched {$this->movie->title}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("**@{$this->logger->username}** just logged *{$this->movie->title}*.")
            ->action('See their log', $this->movie->publicUrl())
            ->line('You can manage your email notification preferences in your profile settings.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'            => 'shared_log',
            'logger_id'       => $this->logger->id,
            'logger_name'     => $this->logger->name,
            'logger_username' => $this->logger->username,
            'movie_id'        => $this->movie->id,
            'movie_title'     => $this->movie->title,
            'movie_url'       => $this->movie->publicUrl(),
            'same_night'      => $this->sameNight,
        ];
    }
}
