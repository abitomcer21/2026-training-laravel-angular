<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Command\CreateFamilyCommand;
use App\Family\Application\Response\CreateFamilyResponse;
use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\ValueObject\FamilyStatus;
use App\Family\Domain\Services\UniqueFamilyName;

class CreateFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private UniqueFamilyName $uniqueFamilyName,
    ) {}

    public function __invoke(CreateFamilyCommand $command): CreateFamilyResponse
    {
        $name   = FamilyName::create($command->name);
        $status = FamilyStatus::create($command->active);

        $this->uniqueFamilyName->check($name, $command->restaurantId);

        $family = Family::dddCreate($name, $status, $command->restaurantId);

        $this->familyRepository->save($family);

        return CreateFamilyResponse::create($family);
    }
}