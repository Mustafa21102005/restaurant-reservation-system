<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->createUser();

        /** @var \App\Models\User $user */
        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect(route('login'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createUser();

        /** @var \App\Models\User $user */
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->createUser();

        /** @var \App\Models\User $user */
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_cannot_be_updated_with_duplicate_email(): void
    {
        $this->createUser(['email' => 'taken@example.com']);
        $user = $this->createUser();

        /** @var \App\Models\User $user */
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'  => 'Test User',
                'email' => 'taken@example.com',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertNotSame('taken@example.com', $user->refresh()->email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->createUser();

        /** @var \App\Models\User $user */
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
        $user = $this->createUser();

        /** @var \App\Models\User $user */
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
}
