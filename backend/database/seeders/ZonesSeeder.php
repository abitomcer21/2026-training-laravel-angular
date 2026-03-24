<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ZonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
            $now = now();
            DB::table('zones')->insert([
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 1',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 2',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 3',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 4',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 5',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 6',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'uuid' => (string)Str::uuid(),
                    'name' => 'Zone 7',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ]);
    }
}
