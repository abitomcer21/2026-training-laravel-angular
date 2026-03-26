<?php

namespace App\Families\Application\GetFamily;

use App\Families\Domain\Interfaces\FamilyRepositoryInterface;

class GetFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $id): ?GetFamilyResponse
    {
        $family = $this->familyRepository->findById($id);

        if (!$family) {
            return null;
        }

        return GetFamilyResponse::create($family);
    }
}
