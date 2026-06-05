<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Query\GetTaxByIdQuery;
use App\Tax\Application\Response\GetTaxByIdResponse;
use App\Tax\Domain\Exceptions\TaxNotFoundException;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class GetTaxByIdHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(GetTaxByIdQuery $query): GetTaxByIdResponse
    {
        $tax = $this->taxRepository->findById($query->id, $query->restaurantId);

        if ($tax === null) {
            throw new TaxNotFoundException($query->id);
        }

        return GetTaxByIdResponse::create($tax);
    }
}