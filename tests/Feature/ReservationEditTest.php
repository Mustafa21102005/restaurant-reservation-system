<?php

namespace Tests\Feature;

use App\Mail\ReservationUpdated;
use App\Models\{Ban, Reservation, TableSeat, Timeout, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationEditTest extends TestCase
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

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    protected function createOngoingReservation(User $user): Reservation
    {
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        return Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'ongoing',
        ]);
    }

    // ── Customer edit ─────────────────────────────────────────────

    public function test_customer_can_edit_their_own_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => 'Updated info',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', [
            'id'       => $reservation->id,
            'table_id' => $newTable->id,
            'status'   => 'ongoing',
        ]);
    }

    public function test_old_table_is_released_and_new_table_is_reserved_on_edit(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $oldTableId  = $reservation->table_id;
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $this->assertDatabaseHas('table_seats', [
            'id'     => $oldTableId,
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('table_seats', [
            'id'     => $newTable->id,
            'status' => 'reserved',
        ]);
    }

    public function test_customer_can_keep_their_existing_table_on_edit(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $reservation->table_id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => 'Same table',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', [
            'id'       => $reservation->id,
            'table_id' => $reservation->table_id,
        ]);
    }

    public function test_customer_cannot_edit_reservation_more_than_once(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        // First edit
        $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $anotherTable = TableSeat::factory()->create(['status' => 'available']);

        // Second edit attempt
        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $anotherTable->id,
                'datetime' => now()->addDays(3)->toDateTimeString(),
                'info'     => null,
            ]);

        $response->assertSessionHasErrors('error');
    }

    public function test_update_is_logged_in_reservation_updates_table(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $this->assertDatabaseHas('reservation_updates', [
            'reservation_id' => $reservation->id,
            'user_id'        => $user->id,
        ]);
    }

    public function test_customer_cannot_edit_another_customers_reservation(): void
    {
        $owner       = $this->createCustomer();
        $other       = $this->createCustomer();
        $reservation = $this->createOngoingReservation($owner);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($other)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $response->assertForbidden();
    }

    public function test_customer_cannot_edit_a_non_ongoing_reservation(): void
    {
        $user  = $this->createCustomer();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $reservation = Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'canceled',
        ]);

        $newTable = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $response->assertForbidden();
    }

    public function test_customer_cannot_select_an_unavailable_table(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $takenTable  = TableSeat::factory()->create(['status' => 'reserved']);

        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $takenTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $response->assertSessionHasErrors('table');
    }

    public function test_banned_customer_cannot_edit_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Test ban',
            'banned_by' => $user->id,
        ]);

        $newTable = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $response->assertSessionHasErrors('error');
    }

    public function test_timed_out_customer_cannot_edit_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $newTable = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        $response->assertSessionHasErrors('error');
    }

    public function test_update_email_is_sent_after_edit(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $this->actingAs($user)
            ->put(route('website.update.reservation', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
            ]);

        Mail::assertQueued(ReservationUpdated::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    // ── Admin edit ────────────────────────────────────────────────

    public function test_admin_can_edit_any_reservation(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)
            ->put(route('reservations.update', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => 'Admin updated',
                'user'     => $customer->id,
            ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'id'       => $reservation->id,
            'table_id' => $newTable->id,
        ]);
    }

    public function test_admin_edit_is_not_logged_in_reservation_updates_table(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $this->actingAs($admin)
            ->put(route('reservations.update', $reservation), [
                'table'    => $newTable->id,
                'datetime' => now()->addDays(2)->toDateTimeString(),
                'info'     => null,
                'user'     => $customer->id,
            ]);

        $this->assertDatabaseMissing('reservation_updates', [
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_edit_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->put(route('website.update.reservation', $reservation), [
            'table'    => $newTable->id,
            'datetime' => now()->addDays(2)->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertRedirect(route('login'));
    }
}
