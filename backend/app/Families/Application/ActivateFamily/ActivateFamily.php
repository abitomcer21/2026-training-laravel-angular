<?php

namespace App\Families\Application\ActivateFamily;

use App\Families\Application\UpdateFamily\UpdateFamilyResponse;
use App\Families\Domain\Entity\Family;
use App\Families\Domain\Interfaces\FamilyRepositoryInterface;

final class ActivateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository
    ) {}

    public function __invoke(string $id): ?UpdateFamilyResponse 
    {
        $family = $this->familyRepository->findById($id);

        if($family === null){
            return null;
        }

        $family->activate();
        $this->familyRepository->save($family);

        return UpdateFamilyResponse::create($family);
    }
}
