<?php

namespace Tests\Feature;

use App\Mail\{ReservationConfirmation, ReservationReminder};
use App\Models\{Ban, TableSeat, Timeout, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        Mail::fake();
    }

    protected function createCustomer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('customer');
        return $user;
    }

    protected function createAvailableTable(): TableSeat
    {
        return TableSeat::factory()->create(['status' => 'available']);
    }

    protected function makeReservation(User $user, array $overrides = [])
    {
        return $this->actingAs($user)->post('/reservation', array_merge([
            'table'    => $this->createAvailableTable()->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => 'Window seat please',
        ], $overrides));
    }

    // ── Happy path ────────────────────────────────────────────────

    public function test_customer_can_make_a_reservation(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        $response = $this->actingAs($user)->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => 'Window seat please',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', [
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'ongoing',
        ]);
    }

    public function test_table_status_is_set_to_reserved_after_reservation(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        $this->actingAs($user)->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $this->assertDatabaseHas('table_seats', [
            'id'     => $table->id,
            'status' => 'reserved',
        ]);
    }

    public function test_verification_code_is_generated_on_reservation(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        $this->actingAs($user)->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $reservation = $user->reservations()->first();
        $this->assertNotNull($reservation->verification_code);
        $this->assertEquals(6, strlen($reservation->verification_code));
    }

    public function test_confirmation_email_is_sent_after_reservation(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        $this->actingAs($user)->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        Mail::assertQueued(ReservationConfirmation::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_reminder_email_is_queued_when_customer_opts_in(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        $this->actingAs($user)->post('/reservation', [
            'table'          => $table->id,
            'datetime'       => now()->addDay()->toDateTimeString(),
            'info'           => null,
            'wants_reminder' => true,
            'reminder_time'  => 30,
        ]);

        Mail::assertQueued(ReservationReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_reminder_email_is_not_queued_when_customer_opts_out(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        $this->actingAs($user)->post('/reservation', [
            'table'          => $table->id,
            'datetime'       => now()->addDay()->toDateTimeString(),
            'info'           => null,
            'wants_reminder' => false,
        ]);

        Mail::assertNotQueued(ReservationReminder::class);
    }

    // ── Restrictions ──────────────────────────────────────────────

    public function test_customer_cannot_make_two_active_reservations(): void
    {
        $user  = $this->createCustomer();
        $table = $this->createAvailableTable();

        // First reservation
        $this->actingAs($user)->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        // Second available table for the second attempt
        $table2 = $this->createAvailableTable();

        $response = $this->actingAs($user)->post('/reservation', [
            'table'    => $table2->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertCount(1, $user->reservations);
    }

    public function test_banned_customer_cannot_make_a_reservation(): void
    {
        $user = $this->createCustomer();

        Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Test ban',
            'banned_by' => $user->id,
        ]);

        $response = $this->makeReservation($user);

        $response->assertSessionHasErrors('error');
        $this->assertCount(0, $user->reservations);
    }

    public function test_timed_out_customer_cannot_make_a_reservation(): void
    {
        $user = $this->createCustomer();

        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $response = $this->makeReservation($user);

        $response->assertSessionHasErrors('error');
        $this->assertCount(0, $user->reservations);
    }

    public function test_unavailable_table_cannot_be_reserved(): void
    {
        $user  = $this->createCustomer();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $response = $this->actingAs($user)->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertCount(0, $user->reservations);
    }

    public function test_unauthenticated_user_cannot_make_a_reservation(): void
    {
        $table = $this->createAvailableTable();

        $response = $this->post('/reservation', [
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertRedirect(route('login'));
    }

    // ── Validation ────────────────────────────────────────────────

    public function test_reservation_requires_a_table(): void
    {
        $user     = $this->createCustomer();
        $response = $this->makeReservation($user, ['table' => null]);

        $response->assertSessionHasErrors('table');
    }

    public function test_reservation_requires_a_future_datetime(): void
    {
        $user     = $this->createCustomer();
        $response = $this->makeReservation($user, ['datetime' => now()->subDay()->toDateTimeString()]);

        $response->assertSessionHasErrors('datetime');
    }

    public function test_reservation_table_must_exist(): void
    {
        $user     = $this->createCustomer();
        $response = $this->makeReservation($user, ['table' => 9999]);

        $response->assertSessionHasErrors('table');
    }
}
