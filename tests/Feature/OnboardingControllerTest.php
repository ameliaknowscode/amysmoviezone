<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication & verification guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_view_onboarding(): void
    {
        $this->get(route('onboarding'))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_cannot_view_onboarding(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect(route('verification.notice'));
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_new_user_sees_onboarding_page(): void
    {
        $user = User::factory()->create(['welcomed_at' => null]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertOk();
    }

    public function test_already_welcomed_user_is_redirected_to_home(): void
    {
        $user = User::factory()->create(['welcomed_at' => now()]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect(route('home'));
    }

    // -------------------------------------------------------------------------
    // Complete
    // -------------------------------------------------------------------------

    public function test_completing_onboarding_sets_welcomed_at(): void
    {
        $user = User::factory()->create(['welcomed_at' => null]);

        $this->actingAs($user)
            ->post(route('onboarding.complete'));

        $this->assertNotNull($user->fresh()->welcomed_at);
    }

    public function test_completing_onboarding_redirects_to_browse(): void
    {
        $user = User::factory()->create(['welcomed_at' => null]);

        $this->actingAs($user)
            ->post(route('onboarding.complete'))
            ->assertRedirect(route('movies.browse'));
    }

    public function test_completing_onboarding_twice_is_idempotent(): void
    {
        $user = User::factory()->create(['welcomed_at' => null]);

        $this->actingAs($user)->post(route('onboarding.complete'));
        $firstWelcomedAt = $user->fresh()->welcomed_at;

        // Completing again should still redirect cleanly
        $this->actingAs($user)
            ->post(route('onboarding.complete'))
            ->assertRedirect(route('movies.browse'));

        // welcomed_at should not have changed
        $this->assertEqualsWithDelta(
            $firstWelcomedAt->timestamp,
            $user->fresh()->welcomed_at->timestamp,
            1
        );
    }

    public function test_unauthenticated_user_cannot_complete_onboarding(): void
    {
        $this->post(route('onboarding.complete'))
            ->assertRedirect(route('login'));
    }
}
