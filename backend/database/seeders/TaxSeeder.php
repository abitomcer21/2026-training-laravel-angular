<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('taxes')->exists()) {
            return;
        }

        $restaurantId = DB::table('restaurants')->first()?->id;

        if (! $restaurantId) {
            return;
        }

        $taxes = [
            ['name' => 'IVA Superreducido', 'percentage' => 4],
            ['name' => 'IVA Reducido', 'percentage' => 10],
            ['name' => 'IVA General', 'percentage' => 21],
        ];

        foreach ($taxes as $tax) {
            DB::table('taxes')->insert([
                'uuid' => Str::uuid(),
                'restaurant_id' => $restaurantId,
                'name' => $tax['name'],
                'percentage' => $tax['percentage'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
