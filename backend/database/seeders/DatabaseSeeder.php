<?php

namespace Database\Seeders;

use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $restaurantId = DB::table('restaurants')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Training Restaurant',
            'legal_name' => 'Training Restaurant SL',
            'tax_id' => 'B00000000',
            'email' => 'restaurant@example.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        EloquentUser::factory()->create([
            'restaurant_id' => $restaurantId,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
