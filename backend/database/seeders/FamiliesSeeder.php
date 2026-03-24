<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FamiliesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        DB::table('families')->insert([
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 1',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 2',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 3',
                'activo' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 4',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 5',
                'activo' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ], 
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 6',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Familia 7',
                'activo' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}
