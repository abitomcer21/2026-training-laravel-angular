<?php

namespace App\Family\Domain\Services;

use App\Family\Domain\Exceptions\FamilyAlreadyExistsException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;

class UniqueFamilyName
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function check(FamilyName $name, int $restaurantId): void
    {
        if ($this->familyRepository->existsByNameAndRestaurant($name, $restaurantId)) {
            throw new FamilyAlreadyExistsException($name->value(), $restaurantId);
        }
    }
}
