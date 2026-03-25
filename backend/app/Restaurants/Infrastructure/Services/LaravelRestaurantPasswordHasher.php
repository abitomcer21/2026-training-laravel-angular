<?php

namespace App\Restaurants\Infrastructure\Services;

use App\Restaurants\Domain\Interfaces\RestaurantPasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

class LaravelRestaurantPasswordHasher implements RestaurantPasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }
}