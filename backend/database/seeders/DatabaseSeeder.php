<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FamiliesSeeder::class,
            TaxesSeeder::class,
            ZonesSeeder::class,
            TablesSeeder::class,
            ProductsSeeder::class,
            SalesSeeder::class,
        ]);
    }
}

