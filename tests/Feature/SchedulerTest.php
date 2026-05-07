<?php

namespace Tests\Feature;

use App\Mail\{NoShowNotification, ReservationWarningNotification, TimeoutExpiredMail};
use App\Models\{Reservation, TableSeat, Timeout, User};
use Carbon\Carbon;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SchedulerTest extends TestCase
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

    protected function createOngoingReservation(User $user, string $datetime): Reservation
    {
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        return Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'ongoing',
            'datetime' => $datetime,
        ]);
    }

    // ── MarkNoShowReservations ────────────────────────────────────

    public function test_reservation_is_marked_no_show_after_10_minutes(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation(
            $user,
            Carbon::now()->subMinutes(11)->toDateTimeString()
        );

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'no_show',
        ]);
    }

    public function test_table_is_released_when_reservation_is_marked_no_show(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation(
            $user,
            Carbon::now()->subMinutes(11)->toDateTimeString()
        );
        $tableId = $reservation->table_id;

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        $this->assertDatabaseHas('table_seats', [
            'id'     => $tableId,
            'status' => 'available',
        ]);
    }

    public function test_no_show_email_is_sent_to_customer(): void
    {
        $user = $this->createCustomer();
        $this->createOngoingReservation(
            $user,
            Carbon::now()->subMinutes(11)->toDateTimeString()
        );

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        Mail::assertQueued(NoShowNotification::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_reservation_not_yet_10_minutes_late_is_not_marked_no_show(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation(
            $user,
            Carbon::now()->subMinutes(5)->toDateTimeString()
        );

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'ongoing',
        ]);
    }

    public function test_warning_email_is_sent_for_reservation_within_5_minutes(): void
    {
        $user = $this->createCustomer();
        $this->createOngoingReservation(
            $user,
            Carbon::now()->addMinutes(3)->toDateTimeString()
        );

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        Mail::assertQueued(ReservationWarningNotification::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_warning_email_is_not_sent_twice_for_same_reservation(): void
    {
        $user        = $this->createCustomer();
        $reservation = $this->createOngoingReservation(
            $user,
            Carbon::now()->addMinutes(3)->toDateTimeString()
        );

        // Mark as already warned
        $reservation->update(['warning_sent_at' => now()]);

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        Mail::assertNotQueued(ReservationWarningNotification::class);
    }

    public function test_non_ongoing_reservations_are_not_marked_no_show(): void
    {
        $user  = $this->createCustomer();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $reservation = Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'canceled',
            'datetime' => Carbon::now()->subMinutes(11)->toDateTimeString(),
        ]);

        $this->artisan('app:mark-no-show-reservations')->assertSuccessful();

        $this->assertDatabaseHas('reservations', [
            'id'     => $reservation->id,
            'status' => 'canceled',
        ]);
    }

    // ── SoftDeleteExpiredTimeouts ─────────────────────────────────

    public function test_expired_timeouts_are_soft_deleted(): void
    {
        $user = $this->createCustomer();

        $timeout = Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->subHour(),
            'timeout_by' => $user->id,
        ]);

        $this->artisan('timeouts:soft-delete-expired')->assertSuccessful();

        $this->assertSoftDeleted('timeouts', ['id' => $timeout->id]);
    }

    public function test_timeout_expired_email_is_sent_after_soft_delete(): void
    {
        $user = $this->createCustomer();

        Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->subHour(),
            'timeout_by' => $user->id,
        ]);

        $this->artisan('timeouts:soft-delete-expired')->assertSuccessful();

        Mail::assertQueued(TimeoutExpiredMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_active_timeouts_are_not_soft_deleted(): void
    {
        $user = $this->createCustomer();

        $timeout = Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Active timeout',
            'expires_at' => now()->addHour(),
            'timeout_by' => $user->id,
        ]);

        $this->artisan('timeouts:soft-delete-expired')->assertSuccessful();

        $this->assertDatabaseHas('timeouts', [
            'id'         => $timeout->id,
            'deleted_at' => null,
        ]);
    }

    public function test_already_soft_deleted_timeouts_are_not_processed_again(): void
    {
        $user = $this->createCustomer();

        $timeout = Timeout::create([
            'user_id'    => $user->id,
            'reason'     => 'Test timeout',
            'expires_at' => now()->subHour(),
            'timeout_by' => $user->id,
        ]);

        $timeout->delete(); // already soft deleted

        Mail::assertNotQueued(TimeoutExpiredMail::class);

        $this->artisan('timeouts:soft-delete-expired')->assertSuccessful();

        // Email should not be sent again
        Mail::assertNotQueued(TimeoutExpiredMail::class);
    }

    public function test_command_outputs_correct_message_when_no_expired_timeouts(): void
    {
        $this->artisan('timeouts:soft-delete-expired')
            ->expectsOutput('No expired timeouts found.')
            ->assertSuccessful();
    }
}
