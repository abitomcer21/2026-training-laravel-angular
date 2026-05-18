<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Query\GetAllFamilyQuery;
use App\Family\Application\Response\GetAllFamilyResponse;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

class GetAllFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(GetAllFamilyQuery $query): GetAllFamilyResponse
    {
        $families = $this->familyRepository->findAllByRestaurant($query->restaurantId);

        return GetAllFamilyResponse::create($families);
    }
}