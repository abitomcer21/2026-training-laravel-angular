<?php

namespace Database\Seeders;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentUser::query()->exists()) {
            return;
        }

        $restaurants = EloquentRestaurant::query()->get();

        if ($restaurants->isEmpty()) {
            return;
        }

        foreach ($restaurants as $index => $restaurant) {
            EloquentUser::factory()
                ->admin()
                ->forRestaurant($restaurant)
                ->create([
                    'name'  => 'Admin Principal',
                    'email' => "admin{$index}@restaurant.test",
                    'pin'   => '1234',
                ]);

            EloquentUser::factory(3)->camarero()->forRestaurant($restaurant)->create();
            EloquentUser::factory(2)->chef()->forRestaurant($restaurant)->create();
            EloquentUser::factory(1)->supervisor()->forRestaurant($restaurant)->create();
        }
    }
}
