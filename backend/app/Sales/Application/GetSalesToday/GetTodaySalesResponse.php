<?php

namespace App\Sales\Application\GetTodaySales;

use App\Sales\Domain\Interfaces\SalesRepositoryInterface;

class GetTodaySalesHandler
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository
    ) {}

    public function __invoke(GetTodaySalesQuery $query): array
    {
        $sales = $this->salesRepository->getTodaySales($query->restaurantId);
        
        return [
            'data' => $sales,
            'message' => 'Ventas del día obtenidas correctamente'
        ];
    }
}