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
        if (!$this->taxesRepository->findById($id)) {
            return false;
        }

        $this->taxesRepository->delete($id);

        return true;
    }
}   