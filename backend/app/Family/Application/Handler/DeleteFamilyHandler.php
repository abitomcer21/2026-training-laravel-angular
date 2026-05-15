<?php

namespace App\Family\Application\Handler;

use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

class DeleteFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $id): void
    {
        $family = $this->familyRepository->findById($id);

        if ($family === null) {
            throw new FamilyNotFoundException($id);
        }

        $this->familyRepository->delete($id);
    }
}
