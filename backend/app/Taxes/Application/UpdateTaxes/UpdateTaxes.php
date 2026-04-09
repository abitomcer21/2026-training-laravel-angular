<?php

namespace App\Taxes\Application\UpdateTaxes;

use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;
use App\Taxes\Domain\ValueObject\TaxName;
use App\Taxes\Domain\ValueObject\TaxPercentage;

class UpdateTaxes
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ) {}

    public function __invoke(
        string $id,
        ?string $name,
        ?int $percentage
    ): ?UpdateTaxesResponse {
        $tax = $this->taxesRepository->findById($id);

        if ($tax === null) {
            return null;
        }

        if ($name === null) {
            $nameVO = $tax->nameVO();
        } else {
            $nameVO = TaxName::create($name);
        }

        if ($percentage === null) {
            $percentageVO = $tax->percentage();
        } else {
            $percentageVO = TaxPercentage::create($percentage);
        }

        $tax = $tax->updateData($nameVO, $percentageVO);
        $this->taxesRepository->save($tax);

        return UpdateTaxesResponse::create($tax);
    }
}