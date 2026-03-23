<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Profile show — follow counts & button
    // -------------------------------------------------------------------------

    public function test_profile_page_shows_follower_and_following_counts(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $carol = User::factory()->create();

        // alice follows bob, carol follows alice
        $alice->following()->attach($bob->id);
        $carol->following()->attach($alice->id);

        $this->get(route('profile.show', $alice->username))
            ->assertOk()
            ->assertSee('1') // 1 follower (carol)
            ->assertSee('1'); // 1 following (bob)
    }

    public function test_follow_button_is_shown_to_authenticated_other_user(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $this->actingAs($alice)
            ->get(route('profile.show', $bob->username))
            ->assertOk()
            ->assertSee('Follow');
    }

    public function test_following_button_is_shown_when_already_following(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $this->actingAs($alice)
            ->get(route('profile.show', $bob->username))
            ->assertOk()
            ->assertSee('Following');
    }

    public function test_follow_button_is_not_shown_on_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.show', $user->username))
            ->assertOk()
            ->assertDontSee('Follow');
    }

    public function test_follow_button_is_not_shown_to_guests(): void
    {
        $user = User::factory()->create();

        $this->get(route('profile.show', $user->username))
            ->assertOk()
            ->assertDontSee('Follow');
    }

    public function test_profile_page_returns_404_for_unknown_username(): void
    {
        $this->get(route('profile.show', 'nobody'))
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Followers list
    // -------------------------------------------------------------------------

    public function test_followers_page_lists_users_who_follow_the_profile(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $bob->following()->attach($alice->id);

        $this->get(route('profile.followers', $alice->username))
            ->assertOk()
            ->assertSee($bob->name);
    }

    public function test_followers_page_shows_empty_state_when_no_followers(): void
    {
        $user = User::factory()->create();

        $this->get(route('profile.followers', $user->username))
            ->assertOk()
            ->assertSee('No followers yet');
    }

    public function test_followers_page_does_not_show_users_who_are_not_followers(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $carol = User::factory()->create();

        $bob->following()->attach($alice->id);

        $this->get(route('profile.followers', $alice->username))
            ->assertOk()
            ->assertDontSee($carol->name);
    }

    public function test_followers_page_returns_404_for_unknown_username(): void
    {
        $this->get(route('profile.followers', 'nobody'))
            ->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Following list
    // -------------------------------------------------------------------------

    public function test_following_page_lists_users_the_profile_follows(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $alice->following()->attach($bob->id);

        $this->get(route('profile.following', $alice->username))
            ->assertOk()
            ->assertSee($bob->name);
    }

    public function test_following_page_shows_empty_state_when_not_following_anyone(): void
    {
        $user = User::factory()->create();

        $this->get(route('profile.following', $user->username))
            ->assertOk()
            ->assertSee('Not following anyone yet');
    }

    public function test_following_page_does_not_show_users_not_being_followed(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $carol = User::factory()->create();

        $alice->following()->attach($bob->id);

        $this->get(route('profile.following', $alice->username))
            ->assertOk()
            ->assertDontSee($carol->name);
    }

    public function test_following_page_returns_404_for_unknown_username(): void
    {
        $this->get(route('profile.following', 'nobody'))
            ->assertNotFound();
    }
}
