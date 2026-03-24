<?php

namespace App\Families\Application\CreateFamilies;

use App\Families\Domain\Entity\Families;
use App\Families\Domain\Interfaces\FamiliesRepositoryInterface;
use App\Families\Domain\ValueObject\FamilyName;
use App\Families\Domain\ValueObject\FamilyStatus;

class CreateFamilies
{
    public function __construct(
        private FamiliesRepositoryInterface $familiesRepository,
    ) {}

    public function __invoke(string $name, bool $activo): CreateFamiliesResponse
    {
        $nameVO = FamilyName::create($name);
        $statusVO = FamilyStatus::create($activo);
        $families = Families::dddCreate($nameVO, $statusVO);
        $this->familiesRepository->save($families);

        return CreateFamiliesResponse::create($families);
    }
}
