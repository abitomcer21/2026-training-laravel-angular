<?php

namespace App\Tax\Application\UpdateTax;

use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use App\Tax\Domain\ValueObject\TaxName;
use App\Tax\Domain\ValueObject\TaxPercentage;

class UpdateTax
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(
        string $id,
        ?string $name,
        ?int $percentage
    ): ?UpdateTaxResponse {
        $tax = $this->taxRepository->findById($id);

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
        $this->taxRepository->save($tax);

        return UpdateTaxResponse::create($tax);
    }
}
