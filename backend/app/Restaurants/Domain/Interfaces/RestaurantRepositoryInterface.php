<?php

namespace App\Restaurants\Domain\Interfaces;

use App\Restaurants\Domain\Entity\Restaurant;

interface RestaurantRepositoryInterface
{
    public function save(Restaurant $restaurant): void;

    public function findById(string $id): ?Restaurant;

    public function findByInternalId(int $id): ?Restaurant;

    public function all(): array;

    public function getInternalIdByUuid(string $uuid): ?int;
}
