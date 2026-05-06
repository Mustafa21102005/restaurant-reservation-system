<?php

namespace Tests\Feature\Auth;

use App\Models\Ban;
use App\Models\Timeout;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function login(User $user, array $overrides = [])
    {
        return $this->post('/login', array_merge([
            'email' => $user->email,
            'password' => 'password',
        ], $overrides));
    }

    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function banUser(User $user): Ban
    {
        return Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Test ban',
            'banned_by' => $user->id,
        ]);
    }

    protected function timeoutUser(User $user, Carbon $expiresAt): Timeout
    {
        return Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => $expiresAt,
            'timeout_by' => $user->id,
        ]);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $user = $this->createUserWithRole('admin');

        $response = $this->login($user);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_customer_is_redirected_to_home_after_login(): void
    {
        $user = $this->createUserWithRole('customer');

        $response = $this->login($user);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->login($user, [
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_banned_customer_cannot_login(): void
    {
        $user = $this->createUserWithRole('customer');

        $this->banUser($user);

        $response = $this->login($user);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_unbanned_customer_can_login(): void
    {
        $user = $this->createUserWithRole('customer');

        // Create a ban then soft-delete it (simulating unban)
        $ban = $this->banUser($user);

        $ban->delete(); // soft-delete = unbanned

        $response = $this->login($user);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_timed_out_customer_cannot_login(): void
    {
        $user = $this->createUserWithRole('customer');

        $this->timeoutUser($user, now()->addHour());

        $response = $this->login($user);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_customer_with_expired_timeout_can_login(): void
    {
        $user = $this->createUserWithRole('customer');

        $this->timeoutUser($user, now()->subHour());

        $response = $this->login($user);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }
}
