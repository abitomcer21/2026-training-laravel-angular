<?php

namespace App\Families\Application\CreateFamily;

use App\Families\Domain\Entity\Family;
use App\Families\Domain\Interfaces\FamilyRepositoryInterface;
use App\Families\Domain\ValueObject\FamilyName;
use App\Families\Domain\ValueObject\FamilyStatus;

class CreateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $name, bool $activo, int $restaurantId): CreateFamilyResponse
    {
        $nameVO   = FamilyName::create($name);
        $statusVO = FamilyStatus::create($activo);
        $family   = Family::dddCreate($nameVO, $statusVO, $restaurantId);
        $this->familyRepository->save($family);

        return CreateFamilyResponse::create($family);
    }
}
