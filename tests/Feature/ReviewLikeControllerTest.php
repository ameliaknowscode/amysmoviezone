<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use App\Notifications\ReviewLiked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewLikeControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_like_a_review(): void
    {
        $review = Review::factory()->create();

        $this->post(route('reviews.likes.store', $review))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_unlike_a_review(): void
    {
        $review = Review::factory()->create();

        $this->delete(route('reviews.likes.destroy', $review))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Store (like)
    // -------------------------------------------------------------------------

    public function test_user_can_like_a_review(): void
    {
        $liker  = User::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id]);

        $this->actingAs($liker)
            ->post(route('reviews.likes.store', $review))
            ->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id'   => $liker->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_liking_twice_does_not_create_duplicate(): void
    {
        $liker  = User::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id]);

        $this->actingAs($liker)->post(route('reviews.likes.store', $review));
        $this->actingAs($liker)->post(route('reviews.likes.store', $review));

        $this->assertDatabaseCount('review_likes', 1);
    }

    public function test_user_cannot_like_their_own_review(): void
    {
        $user   = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('reviews.likes.store', $review))
            ->assertRedirect();

        $this->assertDatabaseEmpty('review_likes');
    }

    // -------------------------------------------------------------------------
    // Destroy (unlike)
    // -------------------------------------------------------------------------

    public function test_user_can_unlike_a_review(): void
    {
        $liker  = User::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id]);

        ReviewLike::create(['user_id' => $liker->id, 'review_id' => $review->id]);

        $this->actingAs($liker)
            ->delete(route('reviews.likes.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id'   => $liker->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_unliking_a_review_not_liked_is_a_no_op(): void
    {
        $liker  = User::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id]);

        $this->actingAs($liker)
            ->delete(route('reviews.likes.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseEmpty('review_likes');
    }

    public function test_unliking_does_not_remove_another_users_like(): void
    {
        $liker1 = User::factory()->create();
        $liker2 = User::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id]);

        ReviewLike::create(['user_id' => $liker1->id, 'review_id' => $review->id]);

        $this->actingAs($liker2)
            ->delete(route('reviews.likes.destroy', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id'   => $liker1->id,
            'review_id' => $review->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Notifications
    // -------------------------------------------------------------------------

    public function test_review_author_is_notified_when_review_is_liked(): void
    {
        Notification::fake();

        $liker  = User::factory()->create();
        $author = User::factory()->create();
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'movie_id' => $movie->id]);

        $this->actingAs($liker)
            ->post(route('reviews.likes.store', $review));

        Notification::assertSentTo($author, ReviewLiked::class);
    }

    public function test_review_author_is_not_notified_on_duplicate_like(): void
    {
        Notification::fake();

        $liker  = User::factory()->create();
        $author = User::factory()->create();
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'movie_id' => $movie->id]);

        ReviewLike::create(['user_id' => $liker->id, 'review_id' => $review->id]);

        $this->actingAs($liker)
            ->post(route('reviews.likes.store', $review));

        Notification::assertNothingSent();
    }

    public function test_like_notification_is_sent_via_mail_when_author_has_email_notifications_enabled(): void
    {
        Notification::fake();

        $liker  = User::factory()->create();
        $author = User::factory()->create(['email_notifications' => true]);
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'movie_id' => $movie->id]);

        $this->actingAs($liker)
            ->post(route('reviews.likes.store', $review));

        Notification::assertSentTo($author, ReviewLiked::class, function ($notification, $channels) {
            return in_array('mail', $channels);
        });
    }

    public function test_like_notification_is_not_sent_via_mail_when_author_has_email_notifications_disabled(): void
    {
        Notification::fake();

        $liker  = User::factory()->create();
        $author = User::factory()->create(['email_notifications' => false]);
        $movie  = Movie::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'movie_id' => $movie->id]);

        $this->actingAs($liker)
            ->post(route('reviews.likes.store', $review));

        Notification::assertSentTo($author, ReviewLiked::class, function ($notification, $channels) {
            return !in_array('mail', $channels);
        });
    }
}
