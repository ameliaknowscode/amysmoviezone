<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'     => 'Test User',
                'username' => 'test_user',
                'email'    => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test_user', $user->username);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'     => 'Test User',
                'username' => $user->username,
                'email'    => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_avatar_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'avatar'   => $file,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_avatar_is_displayed_on_profile_page(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');
        $path = $file->store('avatars', 'public');

        $user = User::factory()->create(['avatar' => $path]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk()->assertSee('storage/' . $path);
    }

    public function test_old_avatar_is_deleted_when_new_one_is_uploaded(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg');
        $oldPath = $oldFile->store('avatars', 'public');

        $user = User::factory()->create(['avatar' => $oldPath]);

        $newFile = UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg');

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'avatar'   => $newFile,
            ]);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_avatar_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'avatar'   => $file,
            ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }

    // -------------------------------------------------------------------------
    // Email notification preferences
    // -------------------------------------------------------------------------

    public function test_email_notifications_default_to_true_for_new_users(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->fresh()->email_notifications);
    }

    public function test_unauthenticated_user_cannot_update_notification_preferences(): void
    {
        $this->patch(route('profile.notifications'), ['email_notifications' => '1'])
            ->assertRedirect(route('login'));
    }

    public function test_email_notifications_can_be_disabled(): void
    {
        $user = User::factory()->create(['email_notifications' => true]);

        $this->actingAs($user)
            ->patch(route('profile.notifications'), ['email_notifications' => '0'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'notifications-updated');

        $this->assertFalse($user->fresh()->email_notifications);
    }

    public function test_email_notifications_can_be_re_enabled(): void
    {
        $user = User::factory()->create(['email_notifications' => false]);

        $this->actingAs($user)
            ->patch(route('profile.notifications'), ['email_notifications' => '1'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'notifications-updated');

        $this->assertTrue($user->fresh()->email_notifications);
    }

    public function test_avatar_upload_rejects_files_over_2mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Create a fake image just over the 2048KB limit
        $file = UploadedFile::fake()->create('big.jpg', 2049, 'image/jpeg');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'avatar'   => $file,
            ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar);
    }
}
