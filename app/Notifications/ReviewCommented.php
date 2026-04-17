<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User   $commenter,
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
            ->subject("@{$this->commenter->username} commented on your review of {$this->review->movie->title}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("**@{$this->commenter->username}** commented on your review of *{$this->review->movie->title}*.")
            ->action('See the comment', $this->review->movie->publicUrl())
            ->line('You can manage your email notification preferences in your profile settings.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'               => 'review_commented',
            'commenter_id'       => $this->commenter->id,
            'commenter_name'     => $this->commenter->name,
            'commenter_username' => $this->commenter->username,
            'review_id'          => $this->review->id,
            'movie_id'           => $this->review->movie_id,
            'movie_title'        => $this->review->movie->title,
            'movie_url'          => $this->review->movie->publicUrl(),
        ];
    }
}
