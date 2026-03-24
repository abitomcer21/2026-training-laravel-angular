<?php

namespace App\Sales\Application\CreateSales;

use App\Sales\Domain\Entity\Sales;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use App\Sales\Domain\ValueObject\Diners;
use App\Shared\Domain\ValueObject\Uuid;

class CreateSales
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(string $tableId, string $openedByUserId, int $diners): CreateSalesResponse
    {
        $dinersVO = Diners::create($diners);
        $sales = Sales::dddCreate(
            Uuid::create($tableId),
            Uuid::create($openedByUserId),
            $dinersVO,
        );
        $this->salesRepository->save($sales);

        return CreateSalesResponse::create($sales);
    }
}
