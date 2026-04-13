<?php

namespace App\Notifications;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListItemAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly MovieList $list,
        public readonly Movie $movie,
        public readonly User $owner,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->owner->name} added to \"{$this->list->name}\" on " . config('app.name'))
            ->greeting("Hi {$notifiable->name}!")
            ->line("**{$this->owner->name}** added **{$this->movie->title}** to their list \"{$this->list->name}\".")
            ->action('View the list', route('lists.show', $this->list))
            ->line('You can manage your email notification preferences in your profile settings.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'list_item_added',
            'list_id'        => $this->list->id,
            'list_name'      => $this->list->name,
            'owner_name'     => $this->owner->name,
            'owner_username' => $this->owner->username,
            'movie_title'    => $this->movie->title,
            'movie_url'      => $this->movie->publicUrl(),
            'list_url'       => route('lists.show', $this->list),
        ];
    }
}
