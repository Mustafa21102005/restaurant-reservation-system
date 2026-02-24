<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\TableSeat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
        ]);
        $admin->assignRole('admin');

        // Create one specific customer user
        $specificCustomer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@gmail.com',
        ]);
        $specificCustomer->assignRole('customer');

        // Create 10 additional users (customers)
        $users = User::factory(10)->create()->each(function ($user) {
            $user->assignRole('customer');
        });

        // Create 3 categories
        $categories = Category::factory(3)->create();

        // Create 3 table seats
        $tables = TableSeat::factory(10)->create();

        // Use all 10 users for reservations
        $reservingUsers = $users;

        // Ensure we don't exceed the number of tables
        $tablesForReservation = $tables->take($reservingUsers->count());

        $reservingUsers->values()->each(function ($user, $index) use ($tablesForReservation) {
            $table = $tablesForReservation[$index];

            Reservation::factory()->create([
                'user_id' => $user->id,
                'table_id' => $table->id,
                'status' => 'ongoing',
            ]);

            $table->update(['status' => 'reserved']);
        });

        // Create 10 products linked to a random category
        $products = Product::factory(5)->create([
            'category_id' => $categories->random()->id,
        ]);

        // Add images for each product via polymorphic relationship
        $products->each(function ($product) {
            $product->imageable()->create([
                'path' => 'https://placehold.co/150',
                'imageable_type' => get_class($product),
                'imageable_id' => $product->id,
            ]);
        });
    }
}
