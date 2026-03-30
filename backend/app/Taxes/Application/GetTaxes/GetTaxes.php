<?php

namespace App\Taxes\Application\GetTaxes;
use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;

class GetTaxes
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ) {}

    public function __invoke(string $id): ?GetTaxesResponse
    {
        $taxes = $this->taxesRepository->findById($id);
        
        if (!$taxes) {
            return null;
        }

        return GetTaxesResponse::create($taxes);
    }
}
