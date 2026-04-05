<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreditImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('admin.credits.import'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_receives_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.credits.import'))
            ->assertForbidden();
    }

    public function test_admin_can_access_page_and_sees_movie_list(): void
    {
        $admin = User::factory()->admin()->create();
        $movie = Movie::factory()->create(['title' => 'Shallow Grave']);

        $this->actingAs($admin)
            ->get(route('admin.credits.import'))
            ->assertOk()
            ->assertSee('Shallow Grave');
    }
}
