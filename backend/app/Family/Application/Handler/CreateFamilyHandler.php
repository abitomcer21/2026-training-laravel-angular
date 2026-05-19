<?php

namespace App\Family\Application\Handler;

use App\Family\Domain\Entity\Family;
use App\Family\Application\Command\CreateFamilyCommand;
use App\Family\Application\Response\CreateFamilyResponse;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\Services\UniqueFamilyName;

class CreateFamilyHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private UniqueFamilyName $uniqueFamilyName,
    ) {}

    public function __invoke(CreateFamilyCommand $command): CreateFamilyResponse
    {
    $this->uniqueFamilyName->check($command->name, $command->restaurantId);

    $family = Family::dddCreate($command->name, $command->active, $command->restaurantId);

    $this->familyRepository->save($family);

    return CreateFamilyResponse::create($family);
    }
}