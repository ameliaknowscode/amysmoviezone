<?php

namespace Tests\Unit;

use App\Models\Movie;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_watchlist_entry_can_be_created_with_factory(): void
    {
        $entry = WatchlistEntry::factory()->create();

        $this->assertInstanceOf(WatchlistEntry::class, $entry);
        $this->assertNotNull($entry->id);
    }

    public function test_watchlist_entry_has_correct_fillable_fields(): void
    {
        $entry = new WatchlistEntry();

        $this->assertEquals(['user_id', 'movie_id', 'list_type', 'watched_at'], $entry->getFillable());
    }

    public function test_watched_at_is_cast_to_date(): void
    {
        $entry = WatchlistEntry::factory()->watched()->create(['watched_at' => '2025-06-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $entry->watched_at);
        $this->assertEquals('2025-06-15', $entry->watched_at->format('Y-m-d'));
    }

    public function test_watched_at_is_nullable(): void
    {
        $entry = WatchlistEntry::factory()->watched()->create(['watched_at' => null]);

        $this->assertNull($entry->watched_at);
    }

    public function test_watchlist_entry_belongs_to_user(): void
    {
        $entry = WatchlistEntry::factory()->create();

        $this->assertInstanceOf(User::class, $entry->user);
    }

    public function test_watchlist_entry_belongs_to_movie(): void
    {
        $entry = WatchlistEntry::factory()->create();

        $this->assertInstanceOf(Movie::class, $entry->movie);
    }

    public function test_list_type_constants_are_defined(): void
    {
        $this->assertSame('want_to_watch', WatchlistEntry::WANT_TO_WATCH);
        $this->assertSame('watched', WatchlistEntry::WATCHED);
    }

    public function test_factory_want_to_watch_state(): void
    {
        $entry = WatchlistEntry::factory()->wantToWatch()->create();

        $this->assertSame(WatchlistEntry::WANT_TO_WATCH, $entry->list_type);
    }

    public function test_factory_watched_state(): void
    {
        $entry = WatchlistEntry::factory()->watched()->create();

        $this->assertSame(WatchlistEntry::WATCHED, $entry->list_type);
    }
}
