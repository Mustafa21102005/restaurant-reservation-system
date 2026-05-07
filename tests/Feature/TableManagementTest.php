<?php

namespace Tests\Feature;

use App\Models\{TableSeat, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableManagementTest extends TestCase
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

    // ── Access control ────────────────────────────────────────────

    public function test_admin_can_access_tables_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('tables.index'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_tables_index(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('tables.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_tables_index(): void
    {
        $response = $this->get(route('tables.index'));

        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────

    public function test_admin_can_access_create_table_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('tables.create'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_table(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('tables.store'), [
            'capacity' => 4,
            'status'   => 'available',
        ]);

        $response->assertRedirect(route('tables.index'));
        $this->assertDatabaseHas('table_seats', [
            'capacity' => 4,
            'status'   => 'available',
        ]);
    }

    public function test_table_requires_capacity(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('tables.store'), [
            'capacity' => null,
            'status'   => 'available',
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    public function test_table_capacity_must_be_at_least_two(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('tables.store'), [
            'capacity' => 1,
            'status'   => 'available',
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    public function test_table_status_must_be_valid(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('tables.store'), [
            'capacity' => 4,
            'status'   => 'reserved', // not allowed on create
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ── Read ──────────────────────────────────────────────────────

    public function test_admin_can_view_a_table(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create();

        $response = $this->actingAs($admin)->get(route('tables.show', $table));

        $response->assertOk();
    }

    // ── Update ────────────────────────────────────────────────────

    public function test_admin_can_access_edit_table_page(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create();

        $response = $this->actingAs($admin)->get(route('tables.edit', $table));

        $response->assertOk();
    }

    public function test_admin_can_update_a_table(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['capacity' => 2]);

        $response = $this->actingAs($admin)->put(route('tables.update', $table), [
            'capacity' => 6,
        ]);

        $response->assertRedirect(route('tables.index'));
        $this->assertDatabaseHas('table_seats', [
            'id'       => $table->id,
            'capacity' => 6,
        ]);
    }

    public function test_update_requires_capacity_of_at_least_two(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['capacity' => 4]);

        $response = $this->actingAs($admin)->put(route('tables.update', $table), [
            'capacity' => 1,
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_admin_can_delete_an_available_table(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['status' => 'available']);

        $response = $this->actingAs($admin)->delete(route('tables.destroy', $table));

        $response->assertRedirect(route('tables.index'));
        $this->assertDatabaseMissing('table_seats', ['id' => $table->id]);
    }

    public function test_admin_cannot_delete_a_reserved_table(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['status' => 'reserved']);

        $response = $this->actingAs($admin)->delete(route('tables.destroy', $table));

        $response->assertRedirect(route('tables.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('table_seats', ['id' => $table->id]);
    }

    // ── Change status ─────────────────────────────────────────────

    public function test_admin_can_toggle_table_status_from_available_to_unavailable(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['status' => 'available']);

        $this->actingAs($admin)->post(route('tables.changeStatus', $table));

        $this->assertDatabaseHas('table_seats', [
            'id'     => $table->id,
            'status' => 'unavailable',
        ]);
    }

    public function test_admin_can_toggle_table_status_from_unavailable_to_available(): void
    {
        $admin = $this->createAdmin();
        $table = TableSeat::factory()->create(['status' => 'unavailable']);

        $this->actingAs($admin)->post(route('tables.changeStatus', $table));

        $this->assertDatabaseHas('table_seats', [
            'id'     => $table->id,
            'status' => 'available',
        ]);
    }
}
