<?php

namespace App\Notifications;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SharedLog extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User  $logger,
        public readonly Movie $movie,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
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
        ];
    }
}
