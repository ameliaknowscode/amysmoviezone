<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Rating;
use App\Models\User;
use App\Models\WatchlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_receives_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Dashboard');
    }

    // -------------------------------------------------------------------------
    // Statistics
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_total_user_count(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(4)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('5'); // 4 regular + 1 admin
    }

    public function test_dashboard_shows_total_movie_count(): void
    {
        $admin = User::factory()->admin()->create();
        Movie::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('3');
    }

    public function test_dashboard_shows_total_ratings_count(): void
    {
        $admin = User::factory()->admin()->create();
        Rating::factory()->count(2)->create(['stars' => 4]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('2');
    }

    public function test_dashboard_shows_recent_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Jane Smith']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Jane Smith');
    }

    public function test_dashboard_shows_recently_joined_section(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(10)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Recently Joined');
    }

    public function test_dashboard_shows_new_users_this_week_indicator(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create(['created_at' => now()->subDays(2)]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('this week');
    }

    public function test_dashboard_shows_watchlist_entries_count(): void
    {
        $admin = User::factory()->admin()->create();
        WatchlistEntry::factory()->count(4)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Watchlist Entries');
    }
}
