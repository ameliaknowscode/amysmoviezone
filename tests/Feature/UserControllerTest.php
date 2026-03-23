<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Authentication guard
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_is_redirected_from_create(): void
    {
        $this->get(route('admin.users.create'))
            ->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_index_lists_users(): void
    {
        $actor = User::factory()->admin()->create();
        $other = User::factory()->create(['name' => 'Jane Doe']);

        $this->actingAs($actor)
            ->get(route('admin.users.index'))
            ->assertSee('Jane Doe');
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.users.create'))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_store_creates_user_and_redirects(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'New User',
                'username'              => 'new_user',
                'email'                 => 'new@example.com',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'     => 'New User',
            'username' => 'new_user',
            'email'    => 'new@example.com',
        ]);
    }

    public function test_store_hashes_password(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Hash Test',
                'username'              => 'hash_test',
                'email'                 => 'hash@example.com',
                'password'              => 'plainpassword',
                'password_confirmation' => 'plainpassword',
            ]);

        $created = User::where('email', 'hash@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('plainpassword', $created->password));
        $this->assertNotEquals('plainpassword', $created->password);
    }

    public function test_store_validates_required_name(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'username'              => 'test_user',
                'email'                 => 'test@example.com',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_store_validates_required_username(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Test User',
                'email'                 => 'test@example.com',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_store_validates_unique_username(): void
    {
        User::factory()->create(['username' => 'taken_user']);
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Another User',
                'username'              => 'taken_user',
                'email'                 => 'another@example.com',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_store_validates_required_email(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Test User',
                'username'              => 'test_user',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_store_validates_unique_email(): void
    {
        $existing = User::factory()->create(['email' => 'taken@example.com']);
        $actor    = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Another User',
                'username'              => 'another_user',
                'email'                 => 'taken@example.com',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_store_validates_password_confirmation(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Test User',
                'username'              => 'test_user',
                'email'                 => 'test@example.com',
                'password'              => 'secret123',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_store_validates_password_minimum_length(): void
    {
        $actor = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name'                  => 'Test User',
                'username'              => 'test_user',
                'email'                 => 'test@example.com',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_user_and_redirects(): void
    {
        $actor  = User::factory()->admin()->create();
        $target = User::factory()->admin()->create();

        $this->actingAs($actor)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $target = User::factory()->admin()->create();

        $this->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }
}
