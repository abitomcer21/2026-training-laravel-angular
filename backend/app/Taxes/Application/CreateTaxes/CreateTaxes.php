<?php

namespace App\Taxes\Application\CreateTaxes;

use App\Taxes\Domain\Entity\Taxes;
use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;
use App\Taxes\Domain\ValueObject\TaxName;
use App\Taxes\Domain\ValueObject\TaxPercentage;

class CreateTaxes
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ) {}

    public function __invoke(string $name, int $percentage): CreateTaxesResponse
    {
        $nameVO = TaxName::create($name);
        $percentageVO = TaxPercentage::create($percentage);
        $taxes = Taxes::dddCreate($nameVO, $percentageVO);
        $this->taxesRepository->save($taxes);

        return CreateTaxesResponse::create($taxes);
    }
}
