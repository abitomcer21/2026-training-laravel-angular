<?php

namespace App\Taxes\Application\GetAllTaxes;

use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;


class GetAllTaxes
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ) {}

    public function __invoke(): GetAllTaxesResponse
    {
        $taxes = $this->taxesRepository->all();
        
        return GetAllTaxesResponse::create($taxes);
    }
}