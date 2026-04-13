<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\User;
use App\Notifications\ListItemAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MovieListFollowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_a_public_list(): void
    {
        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->actingAs($follower)
            ->post(route('lists.follow', $list))
            ->assertRedirect();

        $this->assertTrue($follower->followedLists()->where('movie_list_id', $list->id)->exists());
    }

    public function test_user_cannot_follow_a_private_list(): void
    {
        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->actingAs($follower)
            ->post(route('lists.follow', $list))
            ->assertForbidden();
    }

    public function test_owner_cannot_follow_their_own_list(): void
    {
        $owner = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->actingAs($owner)
            ->post(route('lists.follow', $list))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_follow_a_list(): void
    {
        $list = MovieList::factory()->create(['is_public' => true]);

        $this->post(route('lists.follow', $list))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_unfollow_a_list(): void
    {
        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        $follower->followedLists()->attach($list->id);

        $this->actingAs($follower)
            ->delete(route('lists.unfollow', $list))
            ->assertRedirect();

        $this->assertFalse($follower->followedLists()->where('movie_list_id', $list->id)->exists());
    }

    public function test_following_the_same_list_twice_does_not_duplicate(): void
    {
        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->actingAs($follower)->post(route('lists.follow', $list));
        $this->actingAs($follower)->post(route('lists.follow', $list));

        $this->assertSame(1, $follower->followedLists()->where('movie_list_id', $list->id)->count());
    }

    public function test_adding_a_movie_notifies_list_followers(): void
    {
        Notification::fake();

        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        $movie    = Movie::factory()->create();
        $follower->followedLists()->attach($list->id);

        $this->assertDatabaseHas('movie_list_follows', [
            'user_id'       => $follower->id,
            'movie_list_id' => $list->id,
        ]);

        $this->actingAs($owner)
            ->post(route('lists.movies.store', $list), ['movie_id' => $movie->id]);

        Notification::assertSentTo($follower, ListItemAdded::class);
    }

    public function test_adding_a_movie_does_not_notify_the_owner(): void
    {
        Notification::fake();

        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        $movie    = Movie::factory()->create();
        $follower->followedLists()->attach($list->id);

        $this->actingAs($owner)
            ->post(route('lists.movies.store', $list), ['movie_id' => $movie->id]);

        Notification::assertNotSentTo($owner, ListItemAdded::class);
    }

    public function test_show_page_displays_follow_button_for_non_owner(): void
    {
        $owner    = User::factory()->create();
        $visitor  = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->actingAs($visitor)
            ->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('Follow List');
    }

    public function test_show_page_displays_following_button_when_already_following(): void
    {
        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        $follower->followedLists()->attach($list->id);

        $this->actingAs($follower)
            ->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('Following');
    }

    public function test_show_page_displays_follower_count(): void
    {
        $owner    = User::factory()->create();
        $follower = User::factory()->create();
        $list     = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        $follower->followedLists()->attach($list->id);

        $this->actingAs($owner)
            ->get(route('lists.show', $list))
            ->assertOk()
            ->assertSee('1 follower');
    }
}
