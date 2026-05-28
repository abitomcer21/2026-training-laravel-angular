<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Command\UpdateTaxCommand;
use App\Tax\Application\Response\UpdateTaxResponse;
use App\Tax\Domain\Exceptions\TaxNotFoundException;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class UpdateTaxHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(UpdateTaxCommand $command): UpdateTaxResponse
    {
        $tax = $this->taxRepository->findById($command->id->value());

        if ($tax === null) {
            throw new TaxNotFoundException($command->id->value());
        }

        $name       = $command->name ?? $tax->nameVO();
        $percentage = $command->percentage ?? $tax->percentage();

        $updatedTax = $tax->updateData($name, $percentage);

        $this->taxRepository->save($updatedTax);

        return UpdateTaxResponse::create($updatedTax);
    }
}