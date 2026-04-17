<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RestaurantSeeder::class,
            UserSeeder::class,
            FamilySeeder::class,
            TaxSeeder::class,
            ZonesSeeder::class,
            TablesSeeder::class,
            ProductsSeeder::class,
            OrdersSeeder::class,
            SalesSeeder::class,
        ]);
    }
}
