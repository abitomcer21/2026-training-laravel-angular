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

        $restaurantes = [
            ['name' => 'La Taberna',    'legal_name' => 'La Taberna SL',    'tax_id' => 'ESB12345671', 'email' => 'info@taberna.test'],
            ['name' => 'El Rincón',     'legal_name' => 'El Rincón SL',     'tax_id' => 'ESB12345672', 'email' => 'info@rincon.test'],
            ['name' => 'Casa Pepe',     'legal_name' => 'Casa Pepe SL',     'tax_id' => 'ESB12345673', 'email' => 'info@casapepe.test'],
        ];

        foreach ($restaurantes as $datos) {
            EloquentRestaurant::factory()->create([
                ...$datos,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
