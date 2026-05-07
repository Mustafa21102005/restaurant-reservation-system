<?php

namespace Tests\Feature;

use App\Mail\ReservationCanceled;
use App\Models\{Ban, Reservation, TableSeat, Timeout, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationCancellationTest extends TestCase
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

    // ── Customer cancellation ─────────────────────────────────────

    public function test_customer_can_cancel_their_own_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        $response = $this->actingAs($user)
            ->patch(route('website.reservation.cancel', $reservation));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'canceled',
        ]);
    }

    public function test_table_is_released_when_customer_cancels(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);
        $tableId     = $reservation->table_id;

        $this->actingAs($user)
            ->patch(route('website.reservation.cancel', $reservation));

        $this->assertDatabaseHas('table_seats', [
            'id'     => $tableId,
            'status' => 'available',
        ]);
    }

    public function test_customer_cannot_cancel_another_customers_reservation(): void
    {
        $owner       = $this->createCustomer();
        $other       = $this->createCustomer();
        $reservation = $this->createOngoingReservation($owner);

        $response = $this->actingAs($other)
            ->patch(route('website.reservation.cancel', $reservation));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'ongoing',
        ]);
    }

    public function test_customer_cannot_cancel_a_non_ongoing_reservation(): void
    {
        $user  = $this->createCustomer();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $reservation = Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'canceled',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('website.reservation.cancel', $reservation));

        $response->assertSessionHasErrors('error');
    }

    public function test_banned_customer_cannot_cancel_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        Ban::create([
            'user_id'   => $user->id,
            'reason'    => 'Test ban',
            'banned_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('website.reservation.cancel', $reservation));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'ongoing',
        ]);
    }

    public function test_timed_out_customer_cannot_cancel_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('website.reservation.cancel', $reservation));

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'ongoing',
        ]);
    }

    // ── Admin cancellation ────────────────────────────────────────

    public function test_admin_can_cancel_any_reservation_with_reason(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.cancel', $reservation), [
                'reason' => 'No show',
            ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'canceled',
        ]);
    }

    public function test_cancellation_email_is_sent_to_customer_when_admin_cancels(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $this->actingAs($admin)
            ->patch(route('reservations.cancel', $reservation), [
                'reason' => 'No show',
            ]);

        Mail::assertQueued(ReservationCanceled::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_cancellation_email_is_not_sent_when_customer_cancels(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        $this->actingAs($user)
            ->patch(route('website.reservation.cancel', $reservation));

        Mail::assertNotQueued(ReservationCanceled::class);
    }

    public function test_admin_cancel_requires_a_reason(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.cancel', $reservation), [
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'ongoing',
        ]);
    }

    public function test_table_is_released_when_admin_cancels(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);
        $tableId     = $reservation->table_id;

        $this->actingAs($admin)
            ->patch(route('reservations.cancel', $reservation), [
                'reason' => 'No show',
            ]);

        $this->assertDatabaseHas('table_seats', [
            'id'     => $tableId,
            'status' => 'available',
        ]);
    }

    public function test_unauthenticated_user_cannot_cancel_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation($user);

        $response = $this->patch(route('website.reservation.cancel', $reservation));

        $response->assertRedirect(route('login'));
    }
}
