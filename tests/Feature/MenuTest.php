<?php

namespace Tests\Feature;

use App\Models\{Category, Product};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    // ── Home page ─────────────────────────────────────────────────

    public function test_home_page_can_be_rendered(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_home_page_shows_signature_products(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'name'        => 'Signature Burger',
            'type'        => 'signature',
            'category_id' => $category->id,
        ]);

        Product::factory()->create([
            'name'        => 'Normal Soup',
            'type'        => 'normal',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('home'));

        $response->assertSee('Signature Burger');
        $response->assertDontSee('Normal Soup');
    }

    public function test_home_page_shows_message_when_no_signature_products(): void
    {
        $response = $this->get(route('home'));

        $response->assertSee('There are no Signature Products at the moment');
    }

    // ── Menu page ─────────────────────────────────────────────────

    public function test_menu_page_can_be_rendered(): void
    {
        $response = $this->get(route('menu'));

        $response->assertOk();
    }

    public function test_menu_page_shows_products_grouped_by_category(): void
    {
        $starters = Category::factory()->create(['name' => 'Starters']);
        $mains    = Category::factory()->create(['name' => 'Mains']);

        Product::factory()->create([
            'name'        => 'Spring Rolls',
            'category_id' => $starters->id,
        ]);

        Product::factory()->create([
            'name'        => 'Grilled Steak',
            'category_id' => $mains->id,
        ]);

        $response = $this->get(route('menu'));

        $response->assertSee('Starters');
        $response->assertSee('Spring Rolls');
        $response->assertSee('Mains');
        $response->assertSee('Grilled Steak');
    }

    public function test_menu_page_shows_message_when_no_products(): void
    {
        $response = $this->get(route('menu'));

        $response->assertSee('No products available at the moment');
    }

    public function test_menu_page_shows_product_price(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'name'        => 'Caesar Salad',
            'price'       => 12.50,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('menu'));

        $response->assertSee('12.50');
    }

    public function test_menu_page_shows_all_products_regardless_of_type(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'name'        => 'Signature Dish',
            'type'        => 'signature',
            'category_id' => $category->id,
        ]);

        Product::factory()->create([
            'name'        => 'Normal Dish',
            'type'        => 'normal',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('menu'));

        $response->assertSee('Signature Dish');
        $response->assertSee('Normal Dish');
    }

    public function test_menu_page_is_accessible_without_authentication(): void
    {
        $response = $this->get(route('menu'));

        $response->assertOk();
    }

    public function test_home_page_is_accessible_without_authentication(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
