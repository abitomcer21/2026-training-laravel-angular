<?php

namespace App\Family\Domain\Services;

use App\Family\Domain\Entity\Family;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\Services\SyncProductsStatus;


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

        try {

            $this->familyRepository->beginTransaction();
            $this->familyRepository->save($updatedFamily);

            if ($active !== null) {
                $restaurantId = $family->restaurantId();
                $this->syncProductsStatus->sync($family->id()->value(), $active, $restaurantId);
            }

            $this->familyRepository->commit();

        } catch (\Throwable $e) {
            $this->familyRepository->rollBack();
            throw $e;
        }

        return $updatedFamily;
    }
}