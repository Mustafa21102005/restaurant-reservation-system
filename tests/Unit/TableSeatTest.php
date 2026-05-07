<?php

namespace Tests\Unit;

use App\Models\{Reservation, TableSeat, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableSeatTest extends TestCase
{
    use RefreshDatabase;

    // ── Status ────────────────────────────────────────────────────

    public function test_table_can_be_created_as_available(): void
    {
        $table = TableSeat::factory()->create(['status' => 'available']);

        $this->assertEquals('available', $table->status);
    }

    public function test_table_can_be_created_as_unavailable(): void
    {
        $table = TableSeat::factory()->create(['status' => 'unavailable']);

        $this->assertEquals('unavailable', $table->status);
    }

    public function test_table_can_be_created_as_reserved(): void
    {
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $this->assertEquals('reserved', $table->status);
    }

    public function test_table_status_can_be_updated_to_reserved(): void
    {
        $table = TableSeat::factory()->create(['status' => 'available']);

        $table->update(['status' => 'reserved']);

        $this->assertEquals('reserved', $table->fresh()->status);
    }

    public function test_table_status_can_be_updated_to_available(): void
    {
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $table->update(['status' => 'available']);

        $this->assertEquals('available', $table->fresh()->status);
    }

    public function test_table_status_can_be_toggled(): void
    {
        $table = TableSeat::factory()->create(['status' => 'available']);

        $table->status = $table->status === 'available' ? 'unavailable' : 'available';
        $table->save();

        $this->assertEquals('unavailable', $table->fresh()->status);
    }

    // ── Capacity ──────────────────────────────────────────────────

    public function test_table_stores_correct_capacity(): void
    {
        $table = TableSeat::factory()->create(['capacity' => 6]);

        $this->assertEquals(6, $table->capacity);
    }

    public function test_table_capacity_can_be_updated(): void
    {
        $table = TableSeat::factory()->create(['capacity' => 2]);

        $table->update(['capacity' => 8]);

        $this->assertEquals(8, $table->fresh()->capacity);
    }

    // ── Relationships ─────────────────────────────────────────────

    public function test_table_has_one_reservation(): void
    {
        $user  = User::factory()->create();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
            'status'   => 'ongoing',
        ]);

        $this->assertInstanceOf(Reservation::class, $table->reservations);
    }

    public function test_table_reservation_is_null_when_no_reservation_exists(): void
    {
        $table = TableSeat::factory()->create(['status' => 'available']);

        $this->assertNull($table->reservations);
    }

    public function test_deleting_table_does_not_leave_orphan_reservations(): void
    {
        $user  = User::factory()->create();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $reservation = Reservation::factory()->create([
            'user_id'  => $user->id,
            'table_id' => $table->id,
        ]);

        $table->delete();

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }
}
