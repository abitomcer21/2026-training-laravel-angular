<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        DB::table('taxes')->insert([
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Tax 1',
                'rate' => 0.21,
                'created_at' => $now,
                'updated_at' => $now,
            ],  
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Tax 2',
                'rate' => 0.10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Tax 3',
                'rate' => 0.05,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string)Str::uuid(),
                'name' => 'Tax 4',
                'rate' => 0.15,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ]);
    }
}
