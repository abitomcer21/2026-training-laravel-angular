<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        DB::table('products')->insert([
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 1',
                'description' => 'Descripción del producto 1',
                'price' => 10.99,
                'family_id' => 1,
                'tax_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 2',
                'description' => 'Descripción del producto 2',
                'price' => 20.99,
                'family_id' => 2,
                'tax_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 3',
                'description' => 'Descripción del producto 3',
                'price' => 30.99,
                'family_id' => 3,
                'tax_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 4',
                'description' => 'Descripción del producto 4',
                'price' => 40.99,
                'family_id' => 4,
                'tax_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 5',
                'description' => 'Descripción del producto 5',
                'price' => 50.99,
                'family_id' => 5,
                'tax_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 6',
                'description' => 'Descripción del producto 6',
                'price' => 60.99,
                'family_id' => 6,
                'tax_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Producto 7',
                'description' => 'Descripción del producto 7',
                'price' => 70.99,
                'family_id' => 7,
                'tax_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}
