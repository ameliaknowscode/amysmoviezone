<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_follow(): void
    {
        $target = User::factory()->create();

        $this->post(route('follow.store', $target->username))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_unfollow(): void
    {
        $target = User::factory()->create();

        $this->delete(route('follow.destroy', $target->username))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Follow (store)
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_follow_another_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $this->actingAs($alice)
            ->post(route('follow.store', $bob->username))
            ->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'follower_id'  => $alice->id,
            'following_id' => $bob->id,
        ]);
    }

    public function test_following_twice_does_not_create_duplicate_record(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $this->actingAs($alice)->post(route('follow.store', $bob->username));
        $this->actingAs($alice)->post(route('follow.store', $bob->username));

        $this->assertDatabaseCount('follows', 1);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('follow.store', $user->username))
            ->assertForbidden();

        $this->assertDatabaseEmpty('follows');
    }

    public function test_following_a_nonexistent_user_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('follow.store', 'no-such-user'))
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Unfollow (destroy)
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_unfollow_someone_they_follow(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $this->actingAs($alice)
            ->delete(route('follow.destroy', $bob->username))
            ->assertRedirect();

        $this->assertDatabaseMissing('follows', [
            'follower_id'  => $alice->id,
            'following_id' => $bob->id,
        ]);
    }

    public function test_unfollowing_someone_not_followed_is_a_no_op(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $this->actingAs($alice)
            ->delete(route('follow.destroy', $bob->username))
            ->assertRedirect();

        $this->assertDatabaseEmpty('follows');
    }
}
