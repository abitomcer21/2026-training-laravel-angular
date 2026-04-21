<?php

namespace App\Family\Application\GetAllFamily;

use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

class GetAllFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(?int $restaurantId = null): GetAllFamilyResponse
    {
        if ($restaurantId !== null) {
            $family = $this->familyRepository->allByRestaurantId($restaurantId);
        } else {
            $family = $this->familyRepository->all();
        }

        return GetAllFamilyResponse::create($family);
    }
}
