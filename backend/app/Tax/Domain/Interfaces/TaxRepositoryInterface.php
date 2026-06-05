<?php

namespace App\Tax\Domain\Interfaces;

use App\Tax\Domain\Entity\Tax;

interface TaxRepositoryInterface
{
    public function save(Tax $tax): void;
    public function findById(string $id, int $restaurantId): ?Tax;
    public function findByName(string $name, int $restaurantId): ?Tax;
    public function all(): array;
    public function delete(string $id): void;
    public function findAllByRestaurant(int $restaurantId): array;
}
