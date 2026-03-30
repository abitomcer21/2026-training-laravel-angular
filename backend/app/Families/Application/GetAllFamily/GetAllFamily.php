<?php

namespace App\Families\Application\GetAllFamily;

use App\Families\Domain\Interfaces\FamilyRepositoryInterface;


class GetAllFamilies
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(): GetAllFamilyResponse
    {
        $families = $this->familyRepository->all();
        
        return GetAllFamilyResponse::create($families);
    }
}