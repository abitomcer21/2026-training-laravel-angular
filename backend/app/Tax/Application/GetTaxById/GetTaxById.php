<?php

namespace App\Tax\Application\GetTaxById;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class GetTaxById
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(string $id): ?GetTaxByIdResponse
    {
        $tax = $this->taxRepository->findById($id);
        
        if (!$tax) {
            return null;
        }

        return GetTaxByIdResponse::create($tax);
    }
}
