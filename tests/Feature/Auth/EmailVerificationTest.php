<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function createUnverifiedUser(): User
    {
        return User::factory()->unverified()->create();
    }

    public function test_email_verification_page_can_be_rendered(): void
    {
        $user = $this->createUnverifiedUser();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = $this->createUnverifiedUser();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = $this->createUnverifiedUser();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_already_verified_user_is_redirected(): void
    {
        // already verified user visiting /verify-email should be redirected away
        $user = User::factory()->create(); // factory creates verified by default

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_verification_link_can_be_resent(): void
    {
        $user = $this->createUnverifiedUser();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertSessionHas('status', 'verification-link-sent');
        $response->assertRedirect();
    }

    public function test_verified_user_resending_link_is_redirected(): void
    {
        // already verified user trying to resend should just be redirected
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_email_cannot_be_verified_with_expired_link(): void
    {
        $user = $this->createUnverifiedUser();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(60), // expired
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unauthenticated_user_cannot_access_verification_page(): void
    {
        $response = $this->get('/verify-email');

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_verify_another_users_email(): void
    {
        $user = $this->createUnverifiedUser();
        $otherUser = $this->createUnverifiedUser();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($otherUser)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
