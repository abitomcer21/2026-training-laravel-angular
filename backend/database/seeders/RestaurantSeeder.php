<?php

namespace Database\Seeders;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentRestaurant::query()->exists()) {
            return;
        }

        EloquentRestaurant::factory()->create([
            'name' => 'Restaurant Demo',
            'legal_name' => 'Restaurant Demo SL',
            'tax_id' => 'ESB12345678',
            'email' => 'info@restaurant.test',
            'password' => Hash::make('password'),
        ]);
    }
}
