<?php

namespace App\Families\Application\DesactivateFamily;

use App\Families\Application\UpdateFamily\UpdateFamilyResponse;
use App\Families\Domain\Interfaces\FamilyRepositoryInterface;

final class DesactivateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $id): ?UpdateFamilyResponse
    {
        $family = $this->familyRepository->findById($id);

        if ($family === null) {
            return null;
        }

        $family->deactivate();
        $this->familyRepository->save($family);

        return UpdateFamilyResponse::create($family);
    }
}