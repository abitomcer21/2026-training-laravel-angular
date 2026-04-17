<?php

namespace App\Restaurants\Domain\Interfaces;

interface RestaurantPasswordHasherInterface
{
    public function hash(string $plainPassword): string;
}
