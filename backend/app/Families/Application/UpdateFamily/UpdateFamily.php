<?php

namespace App\Families\Application\UpdateFamily;

use App\Families\Domain\Interfaces\FamilyRepositoryInterface;
use App\Families\Domain\ValueObject\FamilyName;
use App\Families\Domain\ValueObject\FamilyStatus;

class UpdateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $id, string $name, bool $activo): ?UpdateFamilyResponse
    {
        $family = $this->familyRepository->findById($id);

        if (!$family) {
            return null;
        }

        $family->updateName(FamilyName::create($name));
        $family->updateStatus(FamilyStatus::create($activo));
        $this->familyRepository->save($family);

        return UpdateFamilyResponse::create($family);
    }
}