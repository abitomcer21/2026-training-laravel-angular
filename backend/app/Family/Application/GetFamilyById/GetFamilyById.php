<?php

namespace App\Family\Application\GetFamilyById;

use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

class GetFamilyById
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(string $id): GetFamilyByIdResponse
    {
        $family = $this->familyRepository->findById($id);

        if (! $family) {
            throw new FamilyNotFoundException($id);
        }

        return GetFamilyByIdResponse::create($family);
    }
}