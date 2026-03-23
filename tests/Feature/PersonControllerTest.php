<?php

namespace Tests\Feature;

use App\Models\Credit;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Type;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('admin.people.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_create(): void
    {
        $this->get(route('admin.people.create'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_edit(): void
    {
        $person = Person::factory()->create();

        $this->get(route('admin.people.edit', $person))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.people.index'))
            ->assertOk();
    }

    public function test_index_lists_people(): void
    {
        $user   = User::factory()->admin()->create();
        $people = Person::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('admin.people.index'));

        foreach ($people as $person) {
            $response->assertSee($person->name);
        }
    }

    // -------------------------------------------------------------------------
    // Show (public — no auth required)
    // -------------------------------------------------------------------------

    public function test_show_returns_200_for_unauthenticated_user(): void
    {
        $person = Person::factory()->create();

        $this->get(route('people.show', $person))
            ->assertOk();
    }

    public function test_show_displays_person_details(): void
    {
        $person = Person::factory()->create([
            'name'        => 'Jane Smith',
            'nationality' => 'Canadian',
        ]);

        $this->get(route('people.show', $person))
            ->assertSee('Jane Smith')
            ->assertSee('Canadian');
    }

    public function test_show_returns_404_for_nonexistent_person(): void
    {
        $this->get(route('people.show', 999999))
            ->assertNotFound();
    }

    public function test_show_displays_filmography_via_credits(): void
    {
        $person = Person::factory()->create(['name' => 'Tom Hanks']);
        $movie  = Movie::factory()->create(['title' => 'Forrest Gump', 'release_year' => 1994]);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        Credit::factory()->create([
            'person_id' => $person->id,
            'movie_id'  => $movie->id,
            'type_id'   => $type->id,
            'character' => 'Forrest',
        ]);

        $this->get(route('people.show', $person))
            ->assertSee('Forrest Gump')
            ->assertSee('1994')
            ->assertSee('Actor')
            ->assertSee('Forrest');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.people.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_person_with_all_fields_and_redirects(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'name'          => 'John Doe',
                'date_of_birth' => '1975-03-15',
                'nationality'   => 'British',
            ])
            ->assertRedirect(route('admin.people.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('people', [
            'name'          => 'John Doe',
            'date_of_birth' => '1975-03-15',
            'nationality'   => 'British',
        ]);
    }

    public function test_store_creates_person_with_only_required_name(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'name' => 'Jane Doe',
            ])
            ->assertRedirect(route('admin.people.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('people', ['name' => 'Jane Doe']);
    }

    public function test_store_validates_required_name(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'nationality' => 'French',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_future_date_of_birth(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'name'          => 'Future Person',
                'date_of_birth' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_store_rejects_invalid_date_of_birth(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'name'          => 'Some Person',
                'date_of_birth' => 'not-a-date',
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_store_requires_authentication(): void
    {
        $this->post(route('admin.people.store'), ['name' => 'Someone'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('people', ['name' => 'Someone']);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    public function test_edit_returns_200_for_authenticated_user(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.people.edit', $person))
            ->assertOk();
    }

    public function test_edit_prepopulates_fields(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create([
            'name'        => 'Existing Person',
            'nationality' => 'German',
        ]);

        $this->actingAs($user)
            ->get(route('admin.people.edit', $person))
            ->assertSee('Existing Person')
            ->assertSee('German');
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_updates_person_and_redirects(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'name'        => 'New Name',
                'nationality' => 'Spanish',
            ])
            ->assertRedirect(route('admin.people.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('people', ['id' => $person->id, 'name' => 'New Name']);
    }

    public function test_update_validates_required_name(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'nationality' => 'Italian',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_update_rejects_future_date_of_birth(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'name'          => $person->name,
                'date_of_birth' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_update_requires_authentication(): void
    {
        $person = Person::factory()->create(['name' => 'Original']);

        $this->patch(route('admin.people.update', $person), ['name' => 'Changed'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('people', ['id' => $person->id, 'name' => 'Original']);
    }

    // -------------------------------------------------------------------------
    // Credits on edit page
    // -------------------------------------------------------------------------

    public function test_edit_passes_initial_credits_to_view(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();
        $movie  = Movie::factory()->create(['title' => 'Interstellar']);
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);
        Credit::factory()->create([
            'person_id' => $person->id,
            'movie_id'  => $movie->id,
            'type_id'   => $type->id,
            'character' => 'Cooper',
        ]);

        $this->actingAs($user)
            ->get(route('admin.people.edit', $person))
            ->assertOk()
            ->assertSee('Interstellar')
            ->assertSee($type->name);
    }

    public function test_update_saves_credits(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create(['name' => 'Some Actor']);
        $movie  = Movie::factory()->create();
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'name'    => $person->name,
                'credits' => [
                    ['movie_id' => $movie->id, 'type_id' => $type->id, 'character' => 'Hero'],
                ],
            ])
            ->assertRedirect(route('admin.people.index'));

        $this->assertDatabaseHas('credits', [
            'person_id' => $person->id,
            'movie_id'  => $movie->id,
            'type_id'   => $type->id,
            'character' => 'Hero',
        ]);
    }

    public function test_update_syncs_credits_replacing_old_ones(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();
        $movie1 = Movie::factory()->create();
        $movie2 = Movie::factory()->create();
        $type   = Type::firstOrCreate(['name' => 'Actor'], ['is_crew' => false]);

        Credit::factory()->create([
            'person_id' => $person->id,
            'movie_id'  => $movie1->id,
            'type_id'   => $type->id,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'name'    => $person->name,
                'credits' => [
                    ['movie_id' => $movie2->id, 'type_id' => $type->id, 'character' => ''],
                ],
            ]);

        $this->assertDatabaseMissing('credits', ['person_id' => $person->id, 'movie_id' => $movie1->id]);
        $this->assertDatabaseHas('credits',    ['person_id' => $person->id, 'movie_id' => $movie2->id]);
    }

    public function test_update_ignores_incomplete_credit_rows(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();
        $movie  = Movie::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'name'    => $person->name,
                'credits' => [
                    ['movie_id' => $movie->id, 'type_id' => '', 'character' => ''],
                    ['movie_id' => '',          'type_id' => '', 'character' => ''],
                ],
            ]);

        $this->assertSame(0, Credit::where('person_id', $person->id)->count());
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_person_and_redirects(): void
    {
        $user   = User::factory()->admin()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.people.destroy', $person))
            ->assertRedirect(route('admin.people.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('people', ['id' => $person->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $person = Person::factory()->create();

        $this->delete(route('admin.people.destroy', $person))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('people', ['id' => $person->id]);
    }
}
