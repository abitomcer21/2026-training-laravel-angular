<?php

namespace App\Families\Application\GetAllFamilies;

use App\Families\Domain\Interfaces\FamilyRepositoryInterface;

class GetAllFamilies
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    )
    {}

    public function __invoke(): GetAllFamiliesResponse
    {
        $family = $this->familyRepository->all();

        return GetAllFamiliesResponse::create($family);
    }
}