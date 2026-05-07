<?php

namespace Tests\Feature;

use App\Mail\{CustomerBannedMail, CustomerTimeoutMail, CustomerUnbannedMail, TimeoutExpiredMail};
use App\Models\{Ban, Timeout, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        Mail::fake();
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    protected function createCustomer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('customer');
        return $user;
    }

    // ── Access control ────────────────────────────────────────────

    public function test_admin_can_access_customers_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('customers.index'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_customers_index(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('customers.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_customers_index(): void
    {
        $response = $this->get(route('customers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_a_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)->get(route('customers.show', $customer));

        $response->assertOk();
    }

    // ── Ban ───────────────────────────────────────────────────────

    public function test_admin_can_ban_a_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)
            ->post(route('customers.ban', $customer), [
                'reason' => 'Repeated no shows',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bans', [
            'user_id' => $customer->id,
            'reason'  => 'Repeated no shows',
        ]);
    }

    public function test_ban_email_is_sent_to_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $this->actingAs($admin)
            ->post(route('customers.ban', $customer), [
                'reason' => 'Repeated no shows',
            ]);

        Mail::assertQueued(CustomerBannedMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_admin_cannot_ban_an_already_banned_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Ban::create([
            'user_id'   => $customer->id,
            'reason'    => 'First ban',
            'banned_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('customers.ban', $customer), [
                'reason' => 'Second ban attempt',
            ]);

        $response->assertSessionHas('error');
        $this->assertCount(1, $customer->bans);
    }

    public function test_admin_cannot_ban_another_admin(): void
    {
        $admin       = $this->createAdmin();
        $otherAdmin  = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('customers.ban', $otherAdmin), [
                'reason' => 'Test',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('bans', ['user_id' => $otherAdmin->id]);
    }

    // ── Unban ─────────────────────────────────────────────────────

    public function test_admin_can_unban_a_banned_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Ban::create([
            'user_id'   => $customer->id,
            'reason'    => 'Test ban',
            'banned_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('customers.unban', $customer));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('bans', [
            'user_id'    => $customer->id,
            'deleted_at' => null,
        ]);
    }

    public function test_unban_email_is_sent_to_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Ban::create([
            'user_id'   => $customer->id,
            'reason'    => 'Test ban',
            'banned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('customers.unban', $customer));

        Mail::assertQueued(CustomerUnbannedMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_admin_cannot_unban_a_customer_who_is_not_banned(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)
            ->post(route('customers.unban', $customer));

        $response->assertSessionHas('error');
    }

    // ── Timeout ───────────────────────────────────────────────────

    public function test_admin_can_timeout_a_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)
            ->post(route('customers.timeout', $customer), [
                'reason'     => 'Too many cancellations',
                'expires_at' => now()->addHours(2)->toDateTimeString(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('timeouts', [
            'user_id' => $customer->id,
            'reason'  => 'Too many cancellations',
        ]);
    }

    public function test_timeout_email_is_sent_to_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $this->actingAs($admin)
            ->post(route('customers.timeout', $customer), [
                'reason'     => 'Too many cancellations',
                'expires_at' => now()->addHours(2)->toDateTimeString(),
            ]);

        Mail::assertQueued(CustomerTimeoutMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    // ── Untimeout ─────────────────────────────────────────────────

    public function test_admin_can_remove_a_customer_timeout(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Timeout::create([
            'user_id'    => $customer->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('customers.untimeout', $customer));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('timeouts', [
            'user_id'    => $customer->id,
            'deleted_at' => null,
        ]);
    }

    public function test_untimeout_email_is_sent_to_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Timeout::create([
            'user_id'    => $customer->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('customers.untimeout', $customer));

        Mail::assertQueued(TimeoutExpiredMail::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_admin_cannot_remove_timeout_if_none_exists(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)
            ->post(route('customers.untimeout', $customer));

        $response->assertSessionHas('error');
    }
}
