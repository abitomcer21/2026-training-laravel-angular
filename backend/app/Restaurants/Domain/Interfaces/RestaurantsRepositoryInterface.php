<?php

namespace App\Restaurants\Domain\Interfaces;

use App\Restaurants\Domain\Entity\Restaurants;

interface RestaurantsRepositoryInterface
{
    public function save(Restaurants $restaurants): void;

    public function findById(string $id): ?Restaurants;
}