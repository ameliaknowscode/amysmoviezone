<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $channels = ['database'];
        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("@{$this->liker->username} liked your review of {$this->review->movie->title}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("**@{$this->liker->username}** liked your review of *{$this->review->movie->title}*.")
            ->action('View the review', $this->review->movie->publicUrl())
            ->line('You can manage your email notification preferences in your profile settings.');
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
