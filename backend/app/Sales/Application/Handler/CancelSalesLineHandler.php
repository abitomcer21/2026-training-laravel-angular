<?php

namespace App\Sales\Application\Handler;

use App\Sales\Application\Command\CancelSalesLineCommand;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;

class CancelSalesLineHandler
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(CancelSalesLineCommand $command): void
    {
        $line = $this->salesRepository->findSalesLineById($command->salesLineId);

        if ($line === null) {
            throw new \RuntimeException("SalesLine not found: {$command->salesLineId}");
        }

        $this->salesRepository->cancelSalesLine($command->salesLineId);
    }
}
