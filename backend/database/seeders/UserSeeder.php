<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $password = Hash::make('password');

        DB::table('users')->insert([
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'User',
                'email' => 'prueba@prueba.com',
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
