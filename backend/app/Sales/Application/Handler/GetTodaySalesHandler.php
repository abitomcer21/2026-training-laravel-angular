<?php

namespace App\Sales\Application\Handler;

use App\Sales\Application\Query\GetTodaySalesQuery;
use App\Sales\Application\Response\GetTodaySalesResponse;
use App\Sales\Domain\Interfaces\SalesRepositoryInterface;

class GetTodaySalesHandler
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository,
    ) {}

    public function __invoke(GetTodaySalesQuery $query): GetTodaySalesResponse
    {
        $today = date('Y-m-d');

        $sales = $this->salesRepository->getTodaySales($today);

        return GetTodaySalesResponse::create($sales);
    }
}

