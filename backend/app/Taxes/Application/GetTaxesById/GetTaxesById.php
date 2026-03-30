<?php

namespace App\Taxes\Application\GetTaxesById;
use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;

class GetTaxesById
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ) {}

    public function __invoke(string $id): ?GetTaxesByIdResponse
    {
        $taxes = $this->taxesRepository->findById($id);
        
        if (!$taxes) {
            return null;
        }

        return GetTaxesByIdResponse::create($taxes);
    }
}
