<?php

namespace Database\Seeders;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Taxes\Infrastructure\Persistence\Models\EloquentTaxes;
use Illuminate\Database\Seeder;

class TaxesSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentTaxes::query()->exists()) {
            return;
        }

        $restaurant = EloquentRestaurant::query()->first();

        if ($restaurant === null) {
            return;
        }

        $taxes = [
            ['name' => 'IVA Superreducido', 'percentage' => 4],
            ['name' => 'IVA Reducido', 'percentage' => 10],
            ['name' => 'IVA General', 'percentage' => 21],
        ];

        foreach ($taxes as $tax) {
            EloquentTaxes::factory()
                ->forRestaurant($restaurant)
                ->create($tax);
        }
    }
}
