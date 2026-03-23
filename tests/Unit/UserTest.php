<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->id);
    }

    public function test_user_has_correct_fillable_fields(): void
    {
        $user = new User();

        $this->assertEquals(['name', 'username', 'email', 'password', 'avatar', 'ratings_private', 'want_to_watch_private', 'watched_private', 'is_admin'], $user->getFillable());
    }

    public function test_user_password_is_hidden_from_array(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_user_remember_token_is_hidden_from_array(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'test@example.com']);
    }

    // -------------------------------------------------------------------------
    // Following relationships
    // -------------------------------------------------------------------------

    public function test_user_can_follow_another_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $this->assertDatabaseHas('follows', [
            'follower_id'  => $alice->id,
            'following_id' => $bob->id,
        ]);
    }

    public function test_following_relationship_returns_correct_users(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $carol = User::factory()->create();

        $alice->following()->attach([$bob->id, $carol->id]);

        $following = $alice->following()->pluck('users.id');

        $this->assertContains($bob->id, $following);
        $this->assertContains($carol->id, $following);
    }

    public function test_followers_relationship_returns_correct_users(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $carol = User::factory()->create();

        $bob->following()->attach($alice->id);
        $carol->following()->attach($alice->id);

        $followers = $alice->followers()->pluck('users.id');

        $this->assertContains($bob->id, $followers);
        $this->assertContains($carol->id, $followers);
    }

    public function test_is_following_returns_true_when_following(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $this->assertTrue($alice->isFollowing($bob));
    }

    public function test_is_following_returns_false_when_not_following(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $this->assertFalse($alice->isFollowing($bob));
    }

    public function test_deleting_a_user_removes_their_follows(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $alice->delete();

        $this->assertDatabaseMissing('follows', ['follower_id' => $alice->id]);
    }

    public function test_deleting_a_followed_user_removes_the_follow_record(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $bob->delete();

        $this->assertDatabaseMissing('follows', ['following_id' => $bob->id]);
    }
}
