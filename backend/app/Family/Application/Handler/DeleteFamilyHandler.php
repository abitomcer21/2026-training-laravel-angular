<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Command\DeleteFamilyCommand;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

class DeleteFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(DeleteFamilyCommand $command): void
    {
        $family = $this->familyRepository->findById($command->id->value());

        if ($family === null) {
            throw new FamilyNotFoundException($command->id->value());
        }

        $this->familyRepository->delete($command->id->value());
    }
}