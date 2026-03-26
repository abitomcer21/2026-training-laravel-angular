<?php

namespace App\Families\Application\DeleteFamily;

use App\Families\Domain\Interfaces\FamilyRepositoryInterface;

class DeleteFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $id): bool
    {
        $family = $this->familyRepository->findById($id);

        if (!$family) {
            return false;
        }

        $family->markAsDeleted();
        $this->familyRepository->save($family);

        return true;
    }
}
