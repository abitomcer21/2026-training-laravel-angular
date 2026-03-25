<?php

namespace Database\Seeders;

use App\Families\Infraestructure\Persistence\Models\EloquentFamilies;
use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurant;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $restaurant = EloquentRestaurant::factory()->create();

        EloquentUser::factory(1)->admin()->forRestaurant($restaurant)->create();
        EloquentUser::factory(3)->waiter()->forRestaurant($restaurant)->create();
        EloquentUser::factory(2)->chef()->forRestaurant($restaurant)->create();

        EloquentFamilies::factory(5)->forRestaurant($restaurant)->create();
    }
}