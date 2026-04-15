<?php

namespace App\Family\Application\DeleteFamily;

use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

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


