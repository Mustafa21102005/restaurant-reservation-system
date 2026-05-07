<?php

namespace Tests\Feature;

use App\Models\{Category, Product, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        Storage::fake('public');
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

    protected function validProductData(array $overrides = []): array
    {
        $category = Category::factory()->create();

        return array_merge([
            'name'        => 'Grilled Salmon',
            'description' => 'Fresh grilled salmon with herbs and lemon sauce.',
            'price'       => 25.99,
            'category_id' => $category->id,
            'type'        => 'normal',
            'image'       => UploadedFile::fake()->image('dish.jpg'),
        ], $overrides);
    }

    // ── Access control ────────────────────────────────────────────

    public function test_admin_can_access_products_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertOk();
    }

    public function test_customer_cannot_access_products_index(): void
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('products.index'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_products_index(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    // ── Create ────────────────────────────────────────────────────

    public function test_admin_can_access_create_product_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('products.create'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_product_with_image(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData());

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Grilled Salmon',
            'type' => 'normal',
        ]);
    }

    public function test_image_is_stored_on_product_creation(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData());

        $product = Product::where('name', 'Grilled Salmon')->first();
        $this->assertNotNull($product->imageable);
        Storage::disk('public')->assertExists($product->imageable->path);
    }

    public function test_product_requires_name(): void
    {
        $admin    = $this->createAdmin();
        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['name' => null]));

        $response->assertSessionHasErrors('name');
    }

    public function test_product_name_must_be_unique(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create(['name' => 'Unique Dish']);

        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['name' => 'Unique Dish']));

        $response->assertSessionHasErrors('name');
    }

    public function test_product_requires_description_of_at_least_10_characters(): void
    {
        $admin    = $this->createAdmin();
        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['description' => 'Short']));

        $response->assertSessionHasErrors('description');
    }

    public function test_product_requires_price(): void
    {
        $admin    = $this->createAdmin();
        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['price' => null]));

        $response->assertSessionHasErrors('price');
    }

    public function test_product_requires_valid_category(): void
    {
        $admin    = $this->createAdmin();
        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['category_id' => 9999]));

        $response->assertSessionHasErrors('category_id');
    }

    public function test_product_type_must_be_valid(): void
    {
        $admin    = $this->createAdmin();
        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['type' => 'invalid']));

        $response->assertSessionHasErrors('type');
    }

    public function test_product_requires_an_image_on_creation(): void
    {
        $admin    = $this->createAdmin();
        $response = $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData(['image' => null]));

        $response->assertSessionHasErrors('image');
    }

    // ── Read ──────────────────────────────────────────────────────

    public function test_admin_can_view_a_product(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->get(route('products.show', $product));

        $response->assertOk();
    }

    // ── Update ────────────────────────────────────────────────────

    public function test_admin_can_access_edit_product_page(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->get(route('products.edit', $product));

        $response->assertOk();
    }

    public function test_admin_can_update_a_product(): void
    {
        $admin    = $this->createAdmin();
        $product  = Product::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'name'        => 'Updated Dish',
                'description' => 'Updated description that is long enough.',
                'price'       => 30.00,
                'category_id' => $category->id,
                'type'        => 'signature',
            ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'id'   => $product->id,
            'name' => 'Updated Dish',
            'type' => 'signature',
        ]);
    }

    public function test_admin_can_update_product_image(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        // Create initial image
        $oldImage = UploadedFile::fake()->image('old.jpg');
        $this->actingAs($admin)->post(route('products.store'), $this->validProductData([
            'name'  => 'Image Test Dish',
            'image' => $oldImage,
        ]));

        $product  = Product::where('name', 'Image Test Dish')->first();
        $newImage = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($admin)->put(route('products.update', $product), [
            'name'        => 'Image Test Dish',
            'description' => 'Updated description that is long enough.',
            'price'       => 20.00,
            'category_id' => $category->id,
            'type'        => 'normal',
            'image'       => $newImage,
        ]);

        $product->refresh();
        Storage::disk('public')->assertExists($product->imageable->path);
    }

    public function test_update_name_must_be_unique_except_for_current_product(): void
    {
        $admin    = $this->createAdmin();
        $product  = Product::factory()->create(['name' => 'My Dish']);
        $other    = Product::factory()->create(['name' => 'Other Dish']);
        $category = Category::factory()->create();

        // Updating with own name should pass
        $response = $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'name'        => 'My Dish',
                'description' => 'Description long enough to pass.',
                'price'       => 15.00,
                'category_id' => $category->id,
                'type'        => 'normal',
            ]);

        $response->assertSessionHasNoErrors();

        // Updating with another product's name should fail
        $response = $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'name'        => 'Other Dish',
                'description' => 'Description long enough to pass.',
                'price'       => 15.00,
                'category_id' => $category->id,
                'type'        => 'normal',
            ]);

        $response->assertSessionHasErrors('name');
    }

    // ── Delete ────────────────────────────────────────────────────

    public function test_admin_can_delete_a_product(): void
    {
        $admin   = $this->createAdmin();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_image_is_deleted_with_product(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('products.store'), $this->validProductData());

        $product      = Product::where('name', 'Grilled Salmon')->first();
        $imageableId  = $product->imageable->id;

        $this->actingAs($admin)->delete(route('products.destroy', $product));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('images', ['id' => $imageableId]);
    }
}
