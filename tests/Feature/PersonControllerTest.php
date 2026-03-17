<?php

namespace Tests\Feature;

use App\Models\Person;
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
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.people.index'))
            ->assertOk();
    }

    public function test_index_lists_people(): void
    {
        $user   = User::factory()->create();
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

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.people.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_person_with_all_fields_and_redirects(): void
    {
        $user = User::factory()->create();

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
        $user = User::factory()->create();

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
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'nationality' => 'French',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_store_rejects_future_date_of_birth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.people.store'), [
                'name'          => 'Future Person',
                'date_of_birth' => now()->addYear()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_store_rejects_invalid_date_of_birth(): void
    {
        $user = User::factory()->create();

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
        $user   = User::factory()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.people.edit', $person))
            ->assertOk();
    }

    public function test_edit_prepopulates_fields(): void
    {
        $user   = User::factory()->create();
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
        $user   = User::factory()->create();
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
        $user   = User::factory()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.people.update', $person), [
                'nationality' => 'Italian',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_update_rejects_future_date_of_birth(): void
    {
        $user   = User::factory()->create();
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
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_person_and_redirects(): void
    {
        $user   = User::factory()->create();
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
