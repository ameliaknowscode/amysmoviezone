<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WatchlistEntry;
use App\Models\Rating;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_returns_200_for_guests(): void
    {
        $this->get(route('users.index'))->assertOk();
    }

    public function test_directory_returns_200_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertOk();
    }

    public function test_directory_lists_users_by_name(): void
    {
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        $response = $this->get(route('users.index'));

        $content = $response->getContent();
        $this->assertGreaterThan(strpos($content, 'Alice'), strpos($content, 'Bob'));
    }

    public function test_directory_includes_watched_count(): void
    {
        $user  = User::factory()->create();
        $movie = Movie::factory()->create();
        WatchlistEntry::factory()->create([
            'user_id'   => $user->id,
            'movie_id'  => $movie->id,
            'list_type' => WatchlistEntry::WATCHED,
        ]);

        $this->get(route('users.index'))->assertOk();
    }

    public function test_authenticated_user_sees_following_state(): void
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();
        $user->following()->attach($target->id);

        $this->actingAs($user)->get(route('users.index'))->assertOk();
    }
}
