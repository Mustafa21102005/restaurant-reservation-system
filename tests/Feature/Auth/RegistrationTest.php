<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    protected function register(array $overrides = [])
    {
        $data = array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);

        return $this->post('/register', $data);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->register();

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->register([
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->register();

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_requires_matching_passwords(): void
    {
        $response = $this->register([
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_new_user_is_assigned_customer_role(): void
    {
        $this->register();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));
    }
}
