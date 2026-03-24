<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserFollowed extends Notification
{
    use Queueable;

    public function __construct(public readonly User $follower) {}

    public function via($notifiable): array
    {
        return ['database'];
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
