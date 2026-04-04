<?php

namespace Tests\Unit;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewLiked;
use App\Notifications\SharedLog;
use App\Notifications\UserFollowed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // ReviewLiked
    // -------------------------------------------------------------------------

    public function test_review_liked_sends_via_database_channel(): void
    {
        $liker    = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => false]);
        $movie    = Movie::factory()->create();
        $review   = Review::factory()->create(['user_id' => $notified->id, 'movie_id' => $movie->id]);

        $notification = new ReviewLiked($liker, $review->load('movie'));

        $this->assertContains('database', $notification->via($notified));
    }

    public function test_review_liked_also_sends_via_mail_when_email_notifications_enabled(): void
    {
        $liker    = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => true]);
        $movie    = Movie::factory()->create();
        $review   = Review::factory()->create(['user_id' => $notified->id, 'movie_id' => $movie->id]);

        $notification = new ReviewLiked($liker, $review->load('movie'));

        $this->assertContains('mail', $notification->via($notified));
    }

    public function test_review_liked_does_not_send_mail_when_email_notifications_disabled(): void
    {
        $liker    = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => false]);
        $movie    = Movie::factory()->create();
        $review   = Review::factory()->create(['user_id' => $notified->id, 'movie_id' => $movie->id]);

        $notification = new ReviewLiked($liker, $review->load('movie'));

        $this->assertNotContains('mail', $notification->via($notified));
    }

    public function test_review_liked_database_payload_has_correct_keys(): void
    {
        $liker    = User::factory()->create();
        $notified = User::factory()->create();
        $movie    = Movie::factory()->create(['title' => 'The Matrix']);
        $review   = Review::factory()->create(['user_id' => $notified->id, 'movie_id' => $movie->id]);

        $notification = new ReviewLiked($liker, $review->load('movie'));
        $payload      = $notification->toDatabase($notified);

        $this->assertSame('review_liked', $payload['type']);
        $this->assertSame($liker->id, $payload['liker_id']);
        $this->assertSame($review->id, $payload['review_id']);
        $this->assertSame('The Matrix', $payload['movie_title']);
    }

    public function test_review_liked_mail_subject_contains_liker_and_movie(): void
    {
        $liker          = User::factory()->create(['username' => 'johndoe']);
        $notified       = User::factory()->create();
        $movie          = Movie::factory()->create(['title' => 'Alien']);
        $review         = Review::factory()->create(['user_id' => $notified->id, 'movie_id' => $movie->id]);

        $notification   = new ReviewLiked($liker, $review->load('movie'));
        $mail           = $notification->toMail($notified);

        $this->assertStringContainsString('johndoe', $mail->subject);
        $this->assertStringContainsString('Alien', $mail->subject);
    }

    // -------------------------------------------------------------------------
    // SharedLog
    // -------------------------------------------------------------------------

    public function test_shared_log_sends_via_database_channel(): void
    {
        $logger   = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => false]);
        $movie    = Movie::factory()->create();

        $notification = new SharedLog($logger, $movie);

        $this->assertContains('database', $notification->via($notified));
    }

    public function test_shared_log_also_sends_via_mail_when_email_notifications_enabled(): void
    {
        $logger   = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => true]);
        $movie    = Movie::factory()->create();

        $notification = new SharedLog($logger, $movie);

        $this->assertContains('mail', $notification->via($notified));
    }

    public function test_shared_log_does_not_send_mail_when_email_notifications_disabled(): void
    {
        $logger   = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => false]);
        $movie    = Movie::factory()->create();

        $notification = new SharedLog($logger, $movie);

        $this->assertNotContains('mail', $notification->via($notified));
    }

    public function test_shared_log_database_payload_has_correct_keys(): void
    {
        $logger   = User::factory()->create(['username' => 'moviefan']);
        $notified = User::factory()->create();
        $movie    = Movie::factory()->create(['title' => 'Blade Runner']);

        $notification = new SharedLog($logger, $movie);
        $payload      = $notification->toDatabase($notified);

        $this->assertSame('shared_log', $payload['type']);
        $this->assertSame($logger->id, $payload['logger_id']);
        $this->assertSame($movie->id, $payload['movie_id']);
        $this->assertSame('Blade Runner', $payload['movie_title']);
        $this->assertSame('moviefan', $payload['logger_username']);
    }

    public function test_shared_log_mail_subject_contains_logger_and_movie(): void
    {
        $logger   = User::factory()->create(['username' => 'cinephile']);
        $notified = User::factory()->create();
        $movie    = Movie::factory()->create(['title' => 'Vertigo']);

        $notification = new SharedLog($logger, $movie);
        $mail         = $notification->toMail($notified);

        $this->assertStringContainsString('cinephile', $mail->subject);
        $this->assertStringContainsString('Vertigo', $mail->subject);
    }

    // -------------------------------------------------------------------------
    // UserFollowed
    // -------------------------------------------------------------------------

    public function test_user_followed_sends_via_database_channel(): void
    {
        $follower = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => false]);

        $notification = new UserFollowed($follower);

        $this->assertContains('database', $notification->via($notified));
    }

    public function test_user_followed_also_sends_via_mail_when_email_notifications_enabled(): void
    {
        $follower = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => true]);

        $notification = new UserFollowed($follower);

        $this->assertContains('mail', $notification->via($notified));
    }

    public function test_user_followed_does_not_send_mail_when_email_notifications_disabled(): void
    {
        $follower = User::factory()->create();
        $notified = User::factory()->create(['email_notifications' => false]);

        $notification = new UserFollowed($follower);

        $this->assertNotContains('mail', $notification->via($notified));
    }

    public function test_user_followed_database_payload_has_correct_keys(): void
    {
        $follower = User::factory()->create(['username' => 'superfan', 'name' => 'Super Fan']);
        $notified = User::factory()->create();

        $notification = new UserFollowed($follower);
        $payload      = $notification->toDatabase($notified);

        $this->assertSame('user_followed', $payload['type']);
        $this->assertSame($follower->id, $payload['follower_id']);
        $this->assertSame('superfan', $payload['follower_username']);
        $this->assertSame('Super Fan', $payload['follower_name']);
    }

    public function test_user_followed_mail_subject_contains_follower_username(): void
    {
        $follower = User::factory()->create(['username' => 'newbie']);
        $notified = User::factory()->create();

        $notification = new UserFollowed($follower);
        $mail         = $notification->toMail($notified);

        $this->assertStringContainsString('newbie', $mail->subject);
    }
}
