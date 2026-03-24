<?php

namespace Database\Seeders;
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Tables\Infraestructure\Persistence\Models\EloquentTables;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Products\Infraestructure\Persistence\Models\EloquentProducts;

class SalesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();

    DB::table('sales')->insert([
        [
            'uuid' => (string) Str::uuid(),
            'table_id' => EloquentTables::first()->id,
            'user_id' => EloquentUser::first()->id,
            'diners' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'table_id' => EloquentTables::find(2)->id,
            'user_id' => EloquentUser::find(2)->id,
            'diners' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'table_id' => EloquentTables::find(3)->id,
            'user_id' => EloquentUser::find(3)->id,
            'diners' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'table_id' => EloquentTables::find(4)->id,
            'user_id' => EloquentUser::find(4)->id,
            'diners' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'table_id' => EloquentTables::find(5)->id,
            'user_id' => EloquentUser::find(5)->id,
            'diners' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]
    ]);
    DB::table('sales_lines')->insert([
        [
            'uuid' => (string) Str::uuid(),
            'sale_id' => 1,
            'product_id' => EloquentProducts::first()->id,
            'quantity' => 2,
            'price' => 10.99,
            'tax_percentage' => 0.21,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'sale_id' => 1,
            'product_id' => EloquentProducts::find(2)->id,
            'quantity' => 1,
            'price' => 20.99,
            'tax_percentage' => 0.10,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'sale_id' => 2,
            'product_id' => EloquentProducts::find(3)->id,
            'quantity' => 3,
            'price' => 30.99,
            'tax_percentage' => 0.05,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'sale_id' => 3,
            'product_id' => EloquentProducts::find(4)->id,
            'quantity' => 1,
            'price' => 40.99,
            'tax_percentage' => 0.15,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string) Str::uuid(),
            'sale_id' => 4,
            'product_id' => EloquentProducts::find(5)->id,
            'quantity' => 2,
            'price' => 50.99,
            'tax_percentage' => 0.21,
            'created_at' => $now,
            'updated_at' => $now,
        ]
    ]);
    }

}
