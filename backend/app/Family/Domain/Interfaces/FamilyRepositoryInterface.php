<?php

namespace App\Family\Domain\Interfaces;

use App\Family\Domain\Entity\Family;
use App\Family\Domain\ValueObject\FamilyName;

interface FamilyRepositoryInterface
{
    public function save(Family $family): void;

    public function findById(string $id): ?Family;

    public function all(): array;

    public function delete(string $id): void;

    public function findAllByRestaurant(int $restaurantId): array;

    public function existsByNameAndRestaurant(FamilyName $name, int $restaurantId): bool;
}
