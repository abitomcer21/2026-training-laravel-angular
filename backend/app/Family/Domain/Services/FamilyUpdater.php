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

        $this->familyRepository->beginTransaction();

        try {
            $this->saveFamily($updatedFamily);

            if ($active !== null) {
                $this->syncProducts($family->id()->value(), $active);
            }

            $this->familyRepository->commit();

        } catch (\Throwable $e) {
            $this->familyRepository->rollBack();
            throw $e;
        }

        return $updatedFamily;
    }

    private function saveFamily(Family $family): void
    {
        $this->familyRepository->save($family);
    }

    private function syncProducts(string $familyId, bool $active): void
    {
        $this->syncProductsStatus->sync($familyId, $active);
    }
}