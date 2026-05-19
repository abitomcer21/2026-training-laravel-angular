<?php

namespace App\Family\Domain\Services;

use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;

class FamilyUpdater
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
        private SyncProductsStatus $syncProductsStatus,
    ) {}

    public function update(Family $family, ?FamilyName $name, ?bool $active): Family
    {
        $newName   = $name ?? $family->name();
        $newActive = $active ?? $family->active();

        $updatedFamily = $family->updateData($newName, $newActive);

        $this->familyRepository->save($updatedFamily);

        if ($active !== null) {
            ($this->syncProductsStatus)($family->id()->value(), $active);
        }

        return $updatedFamily;
    }
}