<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    $now = now();
    DB::table('tables')->insert([
        [
            'uuid' => (string)Str::uuid(),
            'name' => 'Table 1',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string)Str::uuid(),
            'name' => 'Table 2',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string)Str::uuid(),
            'name' => 'Table 3',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string)Str::uuid(),
            'name' => 'Table 4',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string)Str::uuid(),
            'name' => 'Table 5',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'uuid' => (string)Str::uuid(),
            'name' => 'Table 6',
            'created_at' => $now,
            'updated_at' => $now,
        ]
        ]);
    }
}
