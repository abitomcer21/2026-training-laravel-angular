<?php

namespace App\Family\Application\Handler;

use App\Family\Application\Query\GetFamilyByIdQuery;
use App\Family\Application\Response\GetFamilyByIdResponse;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use App\Family\Domain\Interfaces\FamilyRepositoryInterface;

class GetFamilyByIdHandler
{
    public function __construct(
        private FamilyRepositoryInterface $familyRepository,
    ) {}

    public function __invoke(GetFamilyByIdQuery $query): GetFamilyByIdResponse
    {
        $family = $this->familyRepository->findById($query->id);

        if ($family === null) {
            throw new FamilyNotFoundException();
        }

        return GetFamilyByIdResponse::create($family);
    }
}