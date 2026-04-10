<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('admin.genres.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_create(): void
    {
        $this->get(route('admin.genres.create'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_edit(): void
    {
        $genre = Genre::factory()->create();

        $this->get(route('admin.genres.edit', $genre))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_is_forbidden_from_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.genres.index'))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_admin(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.genres.index'))
            ->assertOk();
    }

    public function test_index_lists_genres(): void
    {
        $user   = User::factory()->admin()->create();
        $genres = Genre::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('admin.genres.index'));

        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_admin(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.genres.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_genre_and_redirects(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.genres.store'), ['name' => 'Science Fiction'])
            ->assertRedirect(route('admin.genres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('genres', [
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
        ]);
    }

    public function test_store_auto_generates_slug_from_name(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.genres.store'), ['name' => 'Romantic Comedy']);

        $this->assertDatabaseHas('genres', ['slug' => 'romantic-comedy']);
    }

    public function test_store_validates_required_name(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.genres.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_duplicate_name(): void
    {
        $user = User::factory()->admin()->create();
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);

        $this->actingAs($user)
            ->post(route('admin.genres.store'), ['name' => 'Horror'])
            ->assertSessionHasErrors('name');
    }

    public function test_store_requires_authentication(): void
    {
        $this->post(route('admin.genres.store'), ['name' => 'Thriller'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('genres', ['name' => 'Thriller']);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_200_for_admin(): void
    {
        $user  = User::factory()->admin()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.genres.edit', $genre))
            ->assertOk();
    }

    public function test_edit_prepopulates_name_field(): void
    {
        $user  = User::factory()->admin()->create();
        $genre = Genre::factory()->create(['name' => 'Documentary', 'slug' => 'documentary']);

        $this->actingAs($user)
            ->get(route('admin.genres.edit', $genre))
            ->assertSee('Documentary');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_updates_genre_and_redirects(): void
    {
        $user  = User::factory()->admin()->create();
        $genre = Genre::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($user)
            ->patch(route('admin.genres.update', $genre), ['name' => 'New Name'])
            ->assertRedirect(route('admin.genres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('genres', [
            'id'   => $genre->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);
    }

    public function test_update_validates_required_name(): void
    {
        $user  = User::factory()->admin()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.genres.update', $genre), [])
            ->assertSessionHasErrors('name');
    }

    public function test_update_rejects_duplicate_name_for_different_genre(): void
    {
        $user  = User::factory()->admin()->create();
        Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);
        $genre = Genre::factory()->create(['name' => 'Thriller', 'slug' => 'thriller']);

        $this->actingAs($user)
            ->patch(route('admin.genres.update', $genre), ['name' => 'Horror'])
            ->assertSessionHasErrors('name');
    }

    public function test_update_allows_keeping_same_name(): void
    {
        $user  = User::factory()->admin()->create();
        $genre = Genre::factory()->create(['name' => 'Horror', 'slug' => 'horror']);

        $this->actingAs($user)
            ->patch(route('admin.genres.update', $genre), ['name' => 'Horror'])
            ->assertRedirect(route('admin.genres.index'));

        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => 'Horror']);
    }

    public function test_update_requires_authentication(): void
    {
        $genre = Genre::factory()->create(['name' => 'Original', 'slug' => 'original']);

        $this->patch(route('admin.genres.update', $genre), ['name' => 'Changed'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => 'Original']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_genre_and_redirects(): void
    {
        $user  = User::factory()->admin()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.genres.destroy', $genre))
            ->assertRedirect(route('admin.genres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $genre = Genre::factory()->create();

        $this->delete(route('admin.genres.destroy', $genre))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }
}
