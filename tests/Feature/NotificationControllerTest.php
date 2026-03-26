<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UserFollowed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_notifications(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_delete_notification(): void
    {
        $this->delete(route('notifications.destroy', 'some-id'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_notifications_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk();
    }

    public function test_notifications_are_marked_as_read_when_page_is_viewed(): void
    {
        $follower = User::factory()->create();
        $user     = User::factory()->create();

        // Create an unread notification
        $user->notify(new UserFollowed($follower));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'read_at'       => null,
        ]);

        $this->actingAs($user)->get(route('notifications.index'));

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'read_at'       => null,
        ]);
    }

    public function test_user_can_only_see_their_own_notifications(): void
    {
        $alice    = User::factory()->create();
        $bob      = User::factory()->create();
        $follower = User::factory()->create();

        $alice->notify(new UserFollowed($follower));

        // Bob should not see Alice's notifications
        $response = $this->actingAs($bob)->get(route('notifications.index'));

        $aliceNotificationId = $alice->notifications()->first()->id;
        $response->assertDontSee($aliceNotificationId);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_user_can_delete_their_own_notification(): void
    {
        $follower = User::factory()->create();
        $user     = User::factory()->create();

        $user->notify(new UserFollowed($follower));
        $notificationId = $user->notifications()->first()->id;

        $this->actingAs($user)
            ->delete(route('notifications.destroy', $notificationId))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notificationId]);
    }

    public function test_user_cannot_delete_another_users_notification(): void
    {
        $follower = User::factory()->create();
        $alice    = User::factory()->create();
        $bob      = User::factory()->create();

        $alice->notify(new UserFollowed($follower));
        $notificationId = $alice->notifications()->first()->id;

        $this->actingAs($bob)
            ->delete(route('notifications.destroy', $notificationId));

        $this->assertDatabaseHas('notifications', ['id' => $notificationId]);
    }

    // -------------------------------------------------------------------------
    // Dispatch & channel selection
    // -------------------------------------------------------------------------

    public function test_follow_notification_is_sent_via_database(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $target   = User::factory()->create();

        $this->actingAs($follower)
            ->post(route('follow.store', $target->username));

        Notification::assertSentTo($target, UserFollowed::class, function ($notification, $channels) {
            return in_array('database', $channels);
        });
    }

    public function test_follow_notification_is_sent_via_mail_when_enabled(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $target   = User::factory()->create(['email_notifications' => true]);

        $this->actingAs($follower)
            ->post(route('follow.store', $target->username));

        Notification::assertSentTo($target, UserFollowed::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }

    public function test_follow_notification_is_not_sent_via_mail_when_disabled(): void
    {
        Notification::fake();

        $follower = User::factory()->create();
        $target   = User::factory()->create(['email_notifications' => false]);

        $this->actingAs($follower)
            ->post(route('follow.store', $target->username));

        Notification::assertSentTo($target, UserFollowed::class, function ($notification, $channels) {
            return !in_array('mail', $channels);
        });
    }
}
