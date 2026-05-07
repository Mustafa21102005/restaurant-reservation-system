<?php

namespace Tests\Feature;

use App\Models\{Reservation, TableSeat, User};
use Carbon\Carbon;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
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

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_dashboard(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('dashboard'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_correct_total_reservation_count(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Reservation::factory()->count(5)->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('reservationCount', 5);
    }

    public function test_dashboard_shows_correct_today_reservations_count(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        // Today's reservations
        Reservation::factory()->count(2)->create([
            'user_id'    => $customer->id,
            'table_id'   => TableSeat::factory()->create()->id,
            'created_at' => Carbon::today(),
        ]);

        // Old reservation
        Reservation::factory()->create([
            'user_id'    => $customer->id,
            'table_id'   => TableSeat::factory()->create()->id,
            'created_at' => Carbon::yesterday(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('todayReservations', 2);
    }

    public function test_dashboard_shows_correct_customer_count(): void
    {
        $admin = $this->createAdmin();

        $this->createCustomer();
        $this->createCustomer();
        $this->createCustomer();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('customerCount', 3);
    }

    public function test_dashboard_shows_correct_ongoing_reservations_count(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Reservation::factory()->count(3)->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'ongoing',
        ]);

        Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'canceled',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('ongoingReservations', 3);
    }

    public function test_dashboard_shows_three_most_recent_reservations(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        Reservation::factory()->count(5)->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('recentReservations', function ($reservations) {
            return $reservations->count() === 3;
        });
    }

    public function test_dashboard_shows_correct_total_tables_count(): void
    {
        $admin = $this->createAdmin();

        TableSeat::factory()->count(4)->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('totalTables', 4);
    }

    public function test_dashboard_shows_correct_occupied_tables_count(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        // Occupied — ongoing
        Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'ongoing',
        ]);

        // Occupied — seated
        Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'seated',
        ]);

        // Not occupied
        Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'done',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('occupiedTables', 2);
    }

    public function test_dashboard_shows_correct_occupancy_rate(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();

        // 2 out of 4 tables occupied = 50%
        TableSeat::factory()->count(2)->create(['status' => 'available']);

        Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'ongoing',
        ]);

        Reservation::factory()->create([
            'user_id'  => $customer->id,
            'table_id' => TableSeat::factory()->create()->id,
            'status'   => 'seated',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('occupancyRate', 50.0);
    }

    public function test_dashboard_occupancy_rate_is_zero_when_no_tables_exist(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('occupancyRate', 0);
    }

    public function test_dashboard_shows_top_customers_by_reservation_count(): void
    {
        $admin = $this->createAdmin();

        $top    = $this->createCustomer();
        $middle = $this->createCustomer();
        $bottom = $this->createCustomer();

        Reservation::factory()->count(5)->create([
            'user_id'  => $top->id,
            'table_id' => TableSeat::factory()->create()->id,
        ]);

        Reservation::factory()->count(3)->create([
            'user_id'  => $middle->id,
            'table_id' => TableSeat::factory()->create()->id,
        ]);

        Reservation::factory()->count(1)->create([
            'user_id'  => $bottom->id,
            'table_id' => TableSeat::factory()->create()->id,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertViewHas('topCustomers', function ($customers) use ($top) {
            return $customers->first()->id === $top->id;
        });
    }
}
