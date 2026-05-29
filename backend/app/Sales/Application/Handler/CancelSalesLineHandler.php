<?php

namespace App\Sales\Application\Handler;

use App\Sales\Application\Command\CancelSalesLineCommand;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use Illuminate\Support\Facades\DB;

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

        $lineTotalCents = $line->price()->cents() * $line->quantity();

        $this->salesRepository->cancelSalesLine($command->salesLineId);

        $sale = $this->salesRepository->findById($line->saleId()->value());

        if ($sale !== null && $sale->total() !== null) {
            $newTotalCents = max(0, $sale->total()->cents() - $lineTotalCents);
            $this->salesRepository->updateSaleTotal($line->saleId()->value(), $newTotalCents);
        }
    }
}
