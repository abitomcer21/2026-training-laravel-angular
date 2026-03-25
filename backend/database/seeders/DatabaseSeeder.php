<?php

namespace Database\Seeders;

use App\Families\Infrastructure\Persistence\Models\EloquentFamilies;
use App\Order\Infrastructure\Persistence\Models\EloquentOrder;
use App\Products\Infrastructure\Persistence\Models\EloquentProducts;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Sales\Infrastructure\Persistence\Models\EloquentSales;
use App\Tables\Infrastructure\Persistence\Models\EloquentTables;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Zones\Infrastructure\Persistence\Models\EloquentZones;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $restaurants = EloquentRestaurant::factory(5)->create();

        foreach ($restaurants as $restaurant) {
            $users = collect();

            $users = $users->merge(EloquentUser::factory(1)->admin()->forRestaurant($restaurant)->create());
            $users = $users->merge(EloquentUser::factory(3)->waiter()->forRestaurant($restaurant)->create());
            $users = $users->merge(EloquentUser::factory(2)->chef()->forRestaurant($restaurant)->create());

            $families = EloquentFamilies::factory(5)->forRestaurant($restaurant)->create();
            $taxes = EloquentTaxes::factory(3)->forRestaurant($restaurant)->create();
            $zones = EloquentZones::factory(3)->forRestaurant($restaurant)->create();

            $tables = collect();
            foreach ($zones as $zone) {
                $tables = $tables->merge(
                    EloquentTables::factory(4)
                        ->forRestaurant($restaurant)
                        ->forZone($zone)
                        ->create()
                );
            }

            for ($i = 0; $i < 20; $i++) {
                EloquentProducts::factory()
                    ->forRestaurant($restaurant)
                    ->forFamily($families->random())
                    ->forTax($taxes->random())
                    ->create();
            }

            for ($i = 0; $i < 10; $i++) {
                $order = EloquentOrder::factory()
                    ->forRestaurant($restaurant)
                    ->forTable($tables->random())
                    ->forUser($users->random())
                    ->create();

                EloquentSales::factory()
                    ->forRestaurant($restaurant)
                    ->forOrder($order)
                    ->forUser($users->random())
                    ->create();
            }
        }
    }
}