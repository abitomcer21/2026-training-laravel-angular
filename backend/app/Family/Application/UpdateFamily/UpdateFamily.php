<?php

namespace App\Family\Application\UpdateFamily;

use App\Family\Domain\Interfaces\FamilyRepositoryInterface;
use App\Family\Domain\ValueObject\FamilyName;
use App\Family\Domain\ValueObject\FamilyStatus;

class UpdateFamily
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(
        string $id,
        ?string $name,
        ?bool $status
    ): ?UpdateFamilyResponse {

        $family = $this->familyRepository->findById($id);

        if (! $family) {
            return null;
        }

        if ($name === null) {
            $nameVO = $family->name();
        } else {
            $nameVO = FamilyName::create($name);
        }

        if ($status === null) {
            $isActive = $family->status();
        } else {
            $isActive = FamilyStatus::create($status);
        }

        $Family = $family->updateData($nameVO, $isActive);
        $this->familyRepository->save($Family);

        return UpdateFamilyResponse::create($family);
    }
}
