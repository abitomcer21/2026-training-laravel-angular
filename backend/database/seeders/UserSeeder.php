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

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        EloquentUser::factory()
            ->admin()
            ->forRestaurant($restaurant)
            ->create([
                'name' => 'Admin Principal',
                'email' => 'admin@restaurant.test',
                'pin' => '1234',
            ]);

        EloquentUser::factory(3)
            ->waiter()
            ->forRestaurant($restaurant)
            ->create();

        EloquentUser::factory(2)
            ->chef()
            ->forRestaurant($restaurant)
            ->create();
    }
}
