<?php

namespace App\Products\Domain\Interfaces;

use App\Products\Domain\Entity\Product;

interface ProductRepositoryInterface
{
    public function findById(string $id, int $restaurantId): ?Product;
    public function findByName(string $name, int $restaurantId): ?Product;
    public function findByFamilyId(string $familyId, int $restaurantId): array;
    public function findAllByRestaurant(int $restaurantId): array;
    public function save(Product $product): void;
    public function delete(string $id): void;
}