<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Response\UpdateFamilyResponse;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\Services\SyncProductsStatus;

class UpdateFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private SyncProductsStatus $syncProductsStatus,
    ) {}

    public function __invoke(UpdateFamilyCommand $command): UpdateFamilyResponse
    {
        $family = $this->familyRepository->findById($command->id);

        if ($family === null) {
            throw new FamilyNotFoundException();
        }

        $name   = $command->name !== null ? FamilyName::create($command->name) : $family->name();
        $active = $command->active !== null ? $command->active : $family->active();

        $updatedFamily = $family->updateData($name, $active);

        $this->familyRepository->save($updatedFamily);

        if ($command->active !== null) {
            ($this->syncProductsStatus)($command->id, $command->active);
        }

        return UpdateFamilyResponse::create($updatedFamily);
    }
}