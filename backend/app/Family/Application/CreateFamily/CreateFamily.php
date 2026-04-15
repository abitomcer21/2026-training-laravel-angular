<?php

namespace App\Family\Application\CreateFamily;

use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\ValueObject\FamilyStatus;

class CreateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $name, bool $active, int $restaurantId): CreateFamilyResponse
    {
        $nameVO   = FamilyName::create($name);
        $statusVO = FamilyStatus::create($active);
        $family   = Family::dddCreate($nameVO, $statusVO, $restaurantId);
        $this->familyRepository->save($family);

        return CreateFamilyResponse::create($family);
    }
}
