<?php

namespace Tests\Feature;

use App\Mail\{ReservationCanceled, ReservationCompleted};
use App\Models\{Discount, Reservation, TableSeat, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationManagementTest extends TestCase
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

    protected function createOngoingReservation(User $customer): Reservation
    {
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        return Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => $table->id,
            'status'   => 'ongoing',
        ]);
    }

    // ── Access control ────────────────────────────────────────────

    public function test_admin_can_access_reservations_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('reservations.index'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_reservations_index(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('reservations.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_reservations_index(): void
    {
        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────

    public function test_admin_can_access_create_reservation_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('reservations.create'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_reservation_for_a_customer(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $table    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)->post(route('reservations.store'), [
            'user'     => $customer->id,
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => 'VIP table',
        ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'user_id'  => $customer->id,
            'table_id' => $table->id,
            'status'   => 'ongoing',
        ]);
    }

    public function test_admin_create_reservation_requires_valid_user(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)->post(route('reservations.store'), [
            'user'     => 9999,
            'table'    => $table->id,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertSessionHasErrors('user');
    }

    public function test_admin_create_reservation_requires_valid_table(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        $response = $this->actingAs($admin)->post(route('reservations.store'), [
            'user'     => $customer->id,
            'table'    => 9999,
            'datetime' => now()->addDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertSessionHasErrors('table');
    }

    public function test_admin_create_reservation_requires_future_datetime(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $table    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)->post(route('reservations.store'), [
            'user'     => $customer->id,
            'table'    => $table->id,
            'datetime' => now()->subDay()->toDateTimeString(),
            'info'     => null,
        ]);

        $response->assertSessionHasErrors('datetime');
    }

    // ── Read ──────────────────────────────────────────────────────

    public function test_admin_can_view_a_reservation(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)->get(route('reservations.show', $reservation));

        $response->assertOk();
    }

    // ── Update ────────────────────────────────────────────────────

    public function test_admin_can_access_edit_reservation_page(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)->get(route('reservations.edit', $reservation));

        $response->assertOk();
    }

    public function test_admin_can_update_a_reservation(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);
        $newTable    = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)->put(route('reservations.update', $reservation), [
            'user'     => $customer->id,
            'table'    => $newTable->id,
            'datetime' => now()->addDays(2)->toDateTimeString(),
            'info'     => 'Updated by admin',
        ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'id'       => $reservation->id,
            'table_id' => $newTable->id,
        ]);
    }

    // ── Cancel ────────────────────────────────────────────────────

    public function test_admin_can_cancel_a_reservation(): void
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

    public function test_cancel_email_is_sent_to_customer(): void
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

    public function test_cancel_requires_a_reason(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.cancel', $reservation), [
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
    }

    // ── Verify ────────────────────────────────────────────────────

    public function test_admin_can_verify_a_reservation_with_correct_code(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.verify', $reservation), [
                'verification_code' => $reservation->verification_code,
            ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'seated',
        ]);
    }

    public function test_admin_cannot_verify_with_incorrect_code(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.verify', $reservation), [
                'verification_code' => 'WRONG1',
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'ongoing',
        ]);
    }

    public function test_admin_can_verify_with_a_valid_discount_code(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $discount = Discount::create([
            'code'           => 'SAVE10',
            'percentage'     => 10,
            'used'           => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('reservations.verify', $reservation), [
                'verification_code' => $reservation->verification_code,
                'discount_code'     => 'SAVE10',
            ]);

        $this->assertDatabaseHas('discounts', [
            'id'   => $discount->id,
            'used' => true,
        ]);
    }

    public function test_admin_cannot_verify_with_invalid_discount_code(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.verify', $reservation), [
                'verification_code' => $reservation->verification_code,
                'discount_code'     => 'INVALID',
            ]);

        $response->assertSessionHasErrors('error');
    }

    // ── Finish ────────────────────────────────────────────────────

    public function test_admin_can_finish_a_reservation(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount' => null,
            ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'done',
        ]);
    }

    public function test_table_is_released_when_reservation_is_finished(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);
        $tableId     = $reservation->table_id;

        $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount' => null,
            ]);

        $this->assertDatabaseHas('table_seats', [
            'id'     => $tableId,
            'status' => 'available',
        ]);
    }

    public function test_completion_email_is_sent_to_customer(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount' => null,
            ]);

        Mail::assertQueued(ReservationCompleted::class, function ($mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function test_discount_is_created_when_admin_sends_discount(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount'       => 'yes',
                'discount_percentage' => 20,
            ]);

        $this->assertDatabaseHas('discounts', [
            'percentage' => 20,
            'used'       => false,
        ]);
    }

    public function test_discount_is_not_created_when_admin_does_not_send_discount(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $discountCountBefore = \App\Models\Discount::count();

        $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount' => null,
            ]);

        $this->assertEquals($discountCountBefore, \App\Models\Discount::count());
    }

    public function test_discount_percentage_is_required_when_send_discount_is_yes(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount'       => 'yes',
                'discount_percentage' => null,
            ]);

        $response->assertSessionHasErrors('discount_percentage');
    }

    public function test_discount_percentage_must_be_between_1_and_100(): void
    {
        $admin       = $this->createAdmin();
        $customer    = $this->createCustomer();
        $reservation = $this->createOngoingReservation($customer);

        $response = $this->actingAs($admin)
            ->patch(route('reservations.finish', $reservation), [
                'send_discount'       => 'yes',
                'discount_percentage' => 150,
            ]);

        $response->assertSessionHasErrors('discount_percentage');
    }
}
