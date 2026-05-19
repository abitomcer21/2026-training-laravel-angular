<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Response\UpdateFamilyResponse;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\Services\FamilyUpdater;

class UpdateFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private FamilyUpdater $familyUpdater,
    ) {}

    public function __invoke(UpdateFamilyCommand $command): UpdateFamilyResponse
    {
        $family = $this->familyRepository->findById($command->id->value());

        if ($family === null) {
            throw new FamilyNotFoundException($command->id->value());
        }

        $updatedFamily = $this->familyUpdater->update(
            family: $family,
            name:   $command->name,
            active: $command->active,
        );

        return UpdateFamilyResponse::create($updatedFamily);
    }
}