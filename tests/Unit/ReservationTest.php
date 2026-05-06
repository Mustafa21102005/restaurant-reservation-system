<?php

namespace Tests\Unit;

use App\Models\Reservation;
use App\Models\TableSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $table = TableSeat::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'table_id' => $table->id,
        ]);

        $this->assertEquals($user->id, $reservation->user->id);
    }

    public function test_reservation_belongs_to_table(): void
    {
        $user = User::factory()->create();
        $table = TableSeat::factory()->create();

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'table_id' => $table->id,
        ]);

        $this->assertInstanceOf(TableSeat::class, $reservation->table);
    }

    public function test_user_has_many_reservations(): void
    {
        $user = User::factory()->create();
        $table = TableSeat::factory()->create();

        Reservation::factory()->count(2)->create([
            'user_id' => $user->id,
            'table_id' => $table->id,
        ]);

        $this->assertCount(2, $user->reservations);
    }
}
