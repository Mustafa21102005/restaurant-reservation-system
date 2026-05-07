<?php

namespace Tests\Feature;

use App\Models\{Category, Product, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
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

    public function test_admin_can_access_categories_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('categories.index'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_categories_index(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('categories.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_categories_index(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────

    public function test_admin_can_access_create_category_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('categories.create'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Desserts',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Desserts']);
    }

    public function test_category_requires_a_name(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => null,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = $this->createAdmin();
        Category::factory()->create(['name' => 'Starters']);

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Starters',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ── Read ──────────────────────────────────────────────────────

    public function test_admin_can_view_a_category(): void
    {
        $admin    = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->get(route('categories.show', $category));

        $response->assertOk();
    }

    // ── Update ────────────────────────────────────────────────────

    public function test_admin_can_access_edit_category_page(): void
    {
        $admin    = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->get(route('categories.edit', $category));

        $response->assertOk();
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin    = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'Mains']);

        $response = $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Main Courses',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'name' => 'Main Courses',
        ]);
    }

    public function test_update_name_must_be_unique_except_for_current_category(): void
    {
        $admin    = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'Drinks']);
        Category::factory()->create(['name' => 'Starters']);

        // Updating with own name should pass
        $response = $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Drinks',
        ]);
        $response->assertSessionHasNoErrors();

        // Updating with another category's name should fail
        $response = $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Starters',
        ]);
        $response->assertSessionHasErrors('name');
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_admin_can_delete_a_category_with_no_products(): void
    {
        $admin    = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_a_category_that_has_products(): void
    {
        $admin    = $this->createAdmin();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
