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
        if (!$this->familyRepository->findById($id)) {
            return false;
        }

        $this->familyRepository->delete($id);

        return true;
    }
}


