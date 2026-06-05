<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Command\UpdateTaxCommand;
use App\Tax\Application\Response\UpdateTaxResponse;
use App\Tax\Domain\Exceptions\TaxNotFoundException;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;
use App\Tax\Domain\Services\TaxUpdater;

class UpdateTaxHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
        private TaxUpdater $taxUpdater,
    ) {}

    public function __invoke(UpdateTaxCommand $command): UpdateTaxResponse
    {
        $tax = $this->taxRepository->findById($command->id->value(), $command->restaurantId);

        if ($tax === null) {
            throw new TaxNotFoundException($command->id->value());
        }

        $updatedTax = $this->taxUpdater->update($tax, $command->name?->value(), $command->percentage?->value());

        $this->taxRepository->save($updatedTax);

        return UpdateTaxResponse::create($updatedTax);
    }
}