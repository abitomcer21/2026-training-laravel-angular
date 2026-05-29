<?php

namespace App\Sales\Application\Handler;

use App\Sales\Application\Command\CancelSaleCommand;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;

class CancelSaleHandler
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(CancelSaleCommand $command): void
    {
        $sale = $this->salesRepository->findById($command->saleId);

        if ($sale === null) {
            throw new \RuntimeException("Sale not found: {$command->saleId}");
        }

        $this->salesRepository->cancelSale($command->saleId);
    }
}
