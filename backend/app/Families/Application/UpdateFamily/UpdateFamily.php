<?php

namespace App\Families\Application\UpdateFamily;

use App\Families\Domain\Interfaces\FamilyRepositoryInterface;
use App\Families\Domain\ValueObject\FamilyName;
use App\Families\Domain\ValueObject\FamilyStatus;

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

        if (!$family) {
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

        $family = $family->updateData($nameVO, $isActive);
        $this->familyRepository->save($family);

        return UpdateFamilyResponse::create($family);
    }
}
