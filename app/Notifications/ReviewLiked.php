<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewLiked extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User   $liker,
        public readonly Review $review,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'            => 'review_liked',
            'liker_id'        => $this->liker->id,
            'liker_name'      => $this->liker->name,
            'liker_username'  => $this->liker->username,
            'review_id'       => $this->review->id,
            'movie_id'        => $this->review->movie_id,
            'movie_title'     => $this->review->movie->title,
            'movie_url'       => $this->review->movie->publicUrl(),
        ];
    }
}
