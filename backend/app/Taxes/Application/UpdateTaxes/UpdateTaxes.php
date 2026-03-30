<?php 

namespace App\Taxes\Application\UpdateTaxes;

use App\Taxes\Domain\Interfaces\TaxesRepositoryInterface;
use App\Taxes\Domain\ValueObject\TaxName;
use App\Taxes\Domain\ValueObject\TaxPercentage;

class UpdateTaxes
{
    public function __construct(
        private TaxesRepositoryInterface $taxesRepository,
    ){}

    public function __invoke(string $id, string $name, int $percentage): ?UpdateTaxesResponse
    {
        $taxes = $this->taxesRepository->findById($id);

        if (!$taxes) {
            return null;
        }

        $nameVO = TaxName::create($name);
        $percentageVO = TaxPercentage::create($percentage);

        $taxes->updateDetails($nameVO, $percentageVO);
        $this->taxesRepository->save($taxes);

        return UpdateTaxesResponse::create($taxes);
    }
}