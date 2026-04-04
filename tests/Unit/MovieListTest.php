<?php

namespace Tests\Unit;

use App\Models\MovieList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieListTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_list_is_visible_to_anyone(): void
    {
        $owner = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => true]);

        $this->assertTrue($list->visibleTo(null));
        $this->assertTrue($list->visibleTo(User::factory()->create()));
        $this->assertTrue($list->visibleTo($owner));
    }

    public function test_private_list_is_visible_to_owner(): void
    {
        $owner = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->assertTrue($list->visibleTo($owner));
    }

    public function test_private_list_is_not_visible_to_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->assertFalse($list->visibleTo($other));
    }

    public function test_private_list_is_not_visible_to_guests(): void
    {
        $owner = User::factory()->create();
        $list  = MovieList::factory()->create(['user_id' => $owner->id, 'is_public' => false]);

        $this->assertFalse($list->visibleTo(null));
    }
}
