<?php

namespace App\Restaurants\Domain\Interfaces;

use App\Restaurants\Domain\Entity\Restaurant;

interface RestaurantRepositoryInterface
{
    public function save(Restaurant $restaurant): void;

    public function findById(string $id): ?Restaurant;
}