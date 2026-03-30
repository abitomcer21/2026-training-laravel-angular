<?php

namespace App\Taxes\Application\DeleteTaxes;

use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;

class DeleteTaxes
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ){}

    public function __invoke(string $id): bool
    {
        $taxes = $this->taxesRepository->findById($id);

        if (!$taxes) {
            return false;
        }

        $taxes->markAsDeleted();
        $this->taxesRepository->save($taxes);

        return true;
    }
}   