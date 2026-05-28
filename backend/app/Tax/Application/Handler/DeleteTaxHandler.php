<?php

namespace App\Tax\Application\Handler;

use App\Tax\Application\Command\DeleteTaxCommand;
use App\Tax\Domain\Exceptions\TaxNotFoundException;
use App\Tax\Domain\Interfaces\TaxRepositoryInterface;

class DeleteTaxHandler
{
    public function __construct(
        private TaxRepositoryInterface $taxRepository,
    ) {}

    public function __invoke(DeleteTaxCommand $command): void
    {
        $tax = $this->taxRepository->findById($command->id->value());

        if ($tax === null) {
            throw new TaxNotFoundException($command->id->value());
        }

        $this->taxRepository->delete($command->id->value());
    }
}