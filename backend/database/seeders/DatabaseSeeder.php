<?php

namespace Database\Seeders;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Products\Infraestructure\Persistence\Models\EloquentProducts;
use App\Restaurants\Infraestructure\Persistence\Models\EloquentRestaurant;
use App\Sales\Infraestructure\Persistence\Models\EloquentSales;
use App\Taxes\Infraestructure\Persistence\Models\EloquentTaxes;
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

        EloquentProducts::factory(20)->forRestaurant($restaurant)->create();

        EloquentTaxes::factory(3)->forRestaurant($restaurant)->create();

        EloquentSales::factory(10)->forRestaurant($restaurant)->create();
    }
}