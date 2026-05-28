<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Query\GetAllTaxQuery;
use App\Tax\Application\Response\GetAllTaxResponse;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class GetAllTaxHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(GetAllTaxQuery $query): GetAllTaxResponse
    {
        $taxes = $this->taxRepository->findAllByRestaurant($query->restaurantId);

        return GetAllTaxResponse::create($taxes);
    }
}