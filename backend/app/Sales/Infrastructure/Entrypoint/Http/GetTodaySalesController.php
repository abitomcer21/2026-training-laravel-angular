<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Domain\Interfaces\SalesRepositoryInterface;
use Illuminate\Http\JsonResponse;

class GetTodaySalesController
{
    public function __construct(
        private SalesRepositoryInterface $salesRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        $today = date('Y-m-d');
        
        $sales = $this->salesRepository->getTodaySales($today);
        
        return response()->json([
            'data' => $sales,
            'message' => 'Ventas del día obtenidas correctamente'
        ]);
    }
}