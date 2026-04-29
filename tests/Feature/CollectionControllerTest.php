<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /** Attach a movie to a collection at $position. */
    private function attachAt(Collection $collection, Movie $movie, int $position): void
    {
        $collection->movies()->attach($movie->id, ['position' => $position]);
    }

    // -------------------------------------------------------------------------
    // Auth: admin routes
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_admin_index(): void
    {
        $this->get(route('admin.collections.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_is_forbidden_from_admin_index(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.collections.index'))
            ->assertForbidden();
    }

    public function test_non_admin_is_forbidden_from_admin_edit(): void
    {
        $collection = Collection::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.collections.edit', $collection))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_attach_movie(): void
    {
        $collection = Collection::factory()->create();
        $movie      = Movie::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.collections.movies.attach', $collection), ['movie_id' => $movie->id])
            ->assertForbidden();

        $this->assertDatabaseCount('collection_movie', 0);
    }

    public function test_non_admin_cannot_reorder(): void
    {
        $collection = Collection::factory()->create();

        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.collections.reorder', $collection), ['order' => []])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Index / Show / Create / Edit
    // -------------------------------------------------------------------------

    public function test_admin_can_view_index_listing_collections(): void
    {
        Collection::factory()->create(['name' => 'Heist Films']);
        Collection::factory()->create(['name' => 'Noir Classics']);

        $this->actingAs($this->admin())
            ->get(route('admin.collections.index'))
            ->assertOk()
            ->assertSee('Heist Films')
            ->assertSee('Noir Classics');
    }

    public function test_admin_can_view_create_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.collections.create'))
            ->assertOk();
    }

    public function test_admin_can_view_edit_page(): void
    {
        $collection = Collection::factory()->create(['name' => 'A24 Picks']);

        $this->actingAs($this->admin())
            ->get(route('admin.collections.edit', $collection))
            ->assertOk()
            ->assertSee('A24 Picks');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_admin_can_create_collection_and_slug_is_auto_generated(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.collections.store'), [
                'name'        => 'Studio Ghibli',
                'description' => 'Films by Studio Ghibli.',
            ])
            ->assertRedirect(route('admin.collections.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('collections', [
            'name' => 'Studio Ghibli',
            'slug' => 'studio-ghibli',
        ]);
    }

    public function test_store_validates_name_required(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.collections.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_store_validates_name_unique(): void
    {
        Collection::factory()->create(['name' => 'Dupe']);

        $this->actingAs($this->admin())
            ->post(route('admin.collections.store'), ['name' => 'Dupe'])
            ->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_collection_and_slug_is_regenerated(): void
    {
        $collection = Collection::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($this->admin())
            ->patch(route('admin.collections.update', $collection), [
                'name'        => 'Brand New Name',
                'description' => 'Updated.',
            ])
            ->assertRedirect(route('admin.collections.index'));

        $this->assertDatabaseHas('collections', [
            'id'   => $collection->id,
            'name' => 'Brand New Name',
            'slug' => 'brand-new-name',
        ]);
    }

    public function test_update_allows_same_name_for_existing_collection(): void
    {
        // Unique-name rule must ignore the current row.
        $collection = Collection::factory()->create(['name' => 'Keepers']);

        $this->actingAs($this->admin())
            ->patch(route('admin.collections.update', $collection), [
                'name' => 'Keepers',
            ])
            ->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_admin_can_destroy_collection(): void
    {
        $collection = Collection::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.collections.destroy', $collection))
            ->assertRedirect(route('admin.collections.index'));

        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }

    // -------------------------------------------------------------------------
    // Attach movie
    // -------------------------------------------------------------------------

    public function test_admin_can_attach_movie_at_position_one(): void
    {
        $collection = Collection::factory()->create();
        $movie      = Movie::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.collections.movies.attach', $collection), ['movie_id' => $movie->id])
            ->assertRedirect(route('admin.collections.edit', $collection));

        $this->assertDatabaseHas('collection_movie', [
            'collection_id' => $collection->id,
            'movie_id'      => $movie->id,
            'position'      => 1,
        ]);
    }

    public function test_attach_assigns_max_position_plus_one(): void
    {
        $collection = Collection::factory()->create();
        $first      = Movie::factory()->create();
        $second     = Movie::factory()->create();
        $third      = Movie::factory()->create();

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.collections.movies.attach', $collection), ['movie_id' => $first->id]);
        $this->actingAs($admin)->post(route('admin.collections.movies.attach', $collection), ['movie_id' => $second->id]);
        $this->actingAs($admin)->post(route('admin.collections.movies.attach', $collection), ['movie_id' => $third->id]);

        $this->assertDatabaseHas('collection_movie', ['movie_id' => $first->id,  'position' => 1]);
        $this->assertDatabaseHas('collection_movie', ['movie_id' => $second->id, 'position' => 2]);
        $this->assertDatabaseHas('collection_movie', ['movie_id' => $third->id,  'position' => 3]);
    }

    public function test_attach_is_idempotent_for_already_attached_movie(): void
    {
        $collection = Collection::factory()->create();
        $movie      = Movie::factory()->create();
        $this->attachAt($collection, $movie, 1);

        $this->actingAs($this->admin())
            ->post(route('admin.collections.movies.attach', $collection), ['movie_id' => $movie->id])
            ->assertRedirect();

        // Still exactly one pivot row, position unchanged
        $this->assertDatabaseCount('collection_movie', 1);
        $this->assertDatabaseHas('collection_movie', [
            'movie_id' => $movie->id,
            'position' => 1,
        ]);
    }

    public function test_attach_validates_movie_id_exists(): void
    {
        $collection = Collection::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.collections.movies.attach', $collection), ['movie_id' => 99999])
            ->assertSessionHasErrors('movie_id');

        $this->assertDatabaseCount('collection_movie', 0);
    }

    // -------------------------------------------------------------------------
    // Detach movie
    // -------------------------------------------------------------------------

    public function test_admin_can_detach_movie(): void
    {
        $collection = Collection::factory()->create();
        $movie      = Movie::factory()->create();
        $this->attachAt($collection, $movie, 1);

        $this->actingAs($this->admin())
            ->delete(route('admin.collections.movies.detach', [$collection, $movie]))
            ->assertRedirect(route('admin.collections.edit', $collection));

        $this->assertDatabaseMissing('collection_movie', [
            'collection_id' => $collection->id,
            'movie_id'      => $movie->id,
        ]);
    }

    public function test_detach_is_idempotent_when_movie_not_in_collection(): void
    {
        $collection = Collection::factory()->create();
        $movie      = Movie::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.collections.movies.detach', [$collection, $movie]))
            ->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // Reorder
    // -------------------------------------------------------------------------

    public function test_admin_can_reorder_movies_in_collection(): void
    {
        $collection = Collection::factory()->create();
        $a = Movie::factory()->create();
        $b = Movie::factory()->create();
        $c = Movie::factory()->create();

        $this->attachAt($collection, $a, 1);
        $this->attachAt($collection, $b, 2);
        $this->attachAt($collection, $c, 3);

        // Reverse the order
        $this->actingAs($this->admin())
            ->postJson(route('admin.collections.reorder', $collection), [
                'order' => [$c->id, $b->id, $a->id],
            ])
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('collection_movie', ['movie_id' => $c->id, 'position' => 1]);
        $this->assertDatabaseHas('collection_movie', ['movie_id' => $b->id, 'position' => 2]);
        $this->assertDatabaseHas('collection_movie', ['movie_id' => $a->id, 'position' => 3]);
    }

    public function test_reorder_validates_order_required(): void
    {
        $collection = Collection::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('admin.collections.reorder', $collection), [])
            ->assertJsonValidationErrors('order');
    }

    public function test_reorder_validates_movie_ids_exist(): void
    {
        $collection = Collection::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('admin.collections.reorder', $collection), ['order' => [99999]])
            ->assertJsonValidationErrors('order.0');
    }

    public function test_reorder_does_not_affect_other_collections(): void
    {
        $a = Collection::factory()->create();
        $b = Collection::factory()->create();
        $movie = Movie::factory()->create();

        $this->attachAt($a, $movie, 1);
        $this->attachAt($b, $movie, 7); // unrelated, should remain 7

        // Reorder collection A
        $this->actingAs($this->admin())
            ->postJson(route('admin.collections.reorder', $a), ['order' => [$movie->id]]);

        $this->assertDatabaseHas('collection_movie', [
            'collection_id' => $b->id,
            'movie_id'      => $movie->id,
            'position'      => 7,
        ]);
    }

    // -------------------------------------------------------------------------
    // Search movies
    // -------------------------------------------------------------------------

    public function test_search_returns_matching_movies_as_json(): void
    {
        $collection = Collection::factory()->create();
        Movie::factory()->create(['title' => 'Star Trek III']);
        Movie::factory()->create(['title' => 'Star Trek IV']);
        Movie::factory()->create(['title' => 'Casablanca']);

        $this->actingAs($this->admin())
            ->getJson(route('admin.collections.movies.search', $collection) . '?q=trek')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['title' => 'Star Trek III'])
            ->assertJsonFragment(['title' => 'Star Trek IV']);
    }

    public function test_search_excludes_movies_already_in_collection(): void
    {
        $collection = Collection::factory()->create();
        $inCollection = Movie::factory()->create(['title' => 'Star Wars']);
        $available    = Movie::factory()->create(['title' => 'Star Trek']);

        $this->attachAt($collection, $inCollection, 1);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.collections.movies.search', $collection) . '?q=star');

        $response->assertOk()->assertJsonCount(1);
        $titles = collect($response->json())->pluck('title')->all();
        $this->assertContains('Star Trek', $titles);
        $this->assertNotContains('Star Wars', $titles);
    }

    public function test_search_returns_empty_for_short_query(): void
    {
        $collection = Collection::factory()->create();
        Movie::factory()->create(['title' => 'A']);

        $this->actingAs($this->admin())
            ->getJson(route('admin.collections.movies.search', $collection) . '?q=a')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_search_returns_id_title_release_year(): void
    {
        $collection = Collection::factory()->create();
        Movie::factory()->create(['title' => 'Vertigo', 'release_year' => 1958]);

        $payload = $this->actingAs($this->admin())
            ->getJson(route('admin.collections.movies.search', $collection) . '?q=vertigo')
            ->assertOk()
            ->json();

        $this->assertCount(1, $payload);
        $this->assertSame('Vertigo', $payload[0]['title']);
        $this->assertSame(1958, $payload[0]['release_year']);
        $this->assertArrayHasKey('id', $payload[0]);
    }

    // -------------------------------------------------------------------------
    // Public routes
    // -------------------------------------------------------------------------

    public function test_public_index_shows_only_collections_with_movies(): void
    {
        $populated = Collection::factory()->create(['name' => 'Has Films']);
        Collection::factory()->create(['name' => 'Empty Collection']);

        $movie = Movie::factory()->create();
        $this->attachAt($populated, $movie, 1);

        $this->get(route('collections.public.index'))
            ->assertOk()
            ->assertSee('Has Films')
            ->assertDontSee('Empty Collection');
    }

    public function test_public_show_returns_collection_by_slug(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Spy Films',
            'slug' => 'spy-films',
        ]);
        $movie = Movie::factory()->create(['title' => 'North by Northwest']);
        $this->attachAt($collection, $movie, 1);

        $this->get(route('collections.public.show', 'spy-films'))
            ->assertOk()
            ->assertSee('Spy Films')
            ->assertSee('North by Northwest');
    }

    public function test_public_show_returns_404_for_unknown_slug(): void
    {
        $this->get(route('collections.public.show', 'nope'))->assertNotFound();
    }

    public function test_public_show_orders_by_pivot_position(): void
    {
        $collection = Collection::factory()->create(['slug' => 'ordered-set']);
        $first  = Movie::factory()->create(['title' => 'AAA First',  'release_year' => 2020]);
        $second = Movie::factory()->create(['title' => 'BBB Second', 'release_year' => 2010]);
        $third  = Movie::factory()->create(['title' => 'CCC Third',  'release_year' => 2000]);

        // Attach in arbitrary release-year order, with explicit pivot positions
        $this->attachAt($collection, $third,  1);
        $this->attachAt($collection, $first,  2);
        $this->attachAt($collection, $second, 3);

        $body = $this->get(route('collections.public.show', 'ordered-set'))
            ->assertOk()
            ->getContent();

        $posThird  = strpos($body, 'CCC Third');
        $posFirst  = strpos($body, 'AAA First');
        $posSecond = strpos($body, 'BBB Second');

        $this->assertTrue($posThird < $posFirst);
        $this->assertTrue($posFirst < $posSecond);
    }

    // -------------------------------------------------------------------------
    // Movie::syncCollections — used by movie edit form
    // -------------------------------------------------------------------------

    public function test_sync_collections_preserves_existing_pivot_positions(): void
    {
        $collectionA = Collection::factory()->create();
        $collectionB = Collection::factory()->create();
        $movie       = Movie::factory()->create();

        $movie->collections()->attach($collectionA->id, ['position' => 5]);
        $movie->collections()->attach($collectionB->id, ['position' => 9]);

        // Sync to the same set — positions must not change
        $movie->syncCollections([$collectionA->id, $collectionB->id]);

        $this->assertDatabaseHas('collection_movie', [
            'movie_id'      => $movie->id,
            'collection_id' => $collectionA->id,
            'position'      => 5,
        ]);
        $this->assertDatabaseHas('collection_movie', [
            'movie_id'      => $movie->id,
            'collection_id' => $collectionB->id,
            'position'      => 9,
        ]);
    }

    public function test_sync_collections_appends_new_attachments_at_max_position_plus_one(): void
    {
        $collection = Collection::factory()->create();
        $existing   = Movie::factory()->create();
        $newcomer   = Movie::factory()->create();

        $this->attachAt($collection, $existing, 7);

        $newcomer->syncCollections([$collection->id]);

        $this->assertDatabaseHas('collection_movie', [
            'movie_id'      => $newcomer->id,
            'collection_id' => $collection->id,
            'position'      => 8, // max(7) + 1
        ]);
    }

    public function test_sync_collections_detaches_collections_not_in_list(): void
    {
        $a     = Collection::factory()->create();
        $b     = Collection::factory()->create();
        $movie = Movie::factory()->create();

        $movie->collections()->attach($a->id, ['position' => 1]);
        $movie->collections()->attach($b->id, ['position' => 1]);

        $movie->syncCollections([$a->id]); // drop B

        $this->assertDatabaseHas('collection_movie',    ['movie_id' => $movie->id, 'collection_id' => $a->id]);
        $this->assertDatabaseMissing('collection_movie', ['movie_id' => $movie->id, 'collection_id' => $b->id]);
    }
}
