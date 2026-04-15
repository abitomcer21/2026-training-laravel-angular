<?php

namespace App\Tax\Application\GetAllTax;

use App\Tax\Domain\Interfaces\TaxRepositoryInterface;


class GetAllTax
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(): GetAllTaxResponse
    {
        $tax = $this->taxRepository->all();
        
        return GetAllTaxResponse::create($tax);
    }
}