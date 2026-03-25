<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\AddSalesLine\AddSalesLine;
use App\Sales\Application\AddSalesLine\AddSalesLineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddLineController
{
    public function __construct(
        private AddSalesLine $addSalesLine,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_id' => 'required|string|uuid',
            'order_line_id' => 'required|string|uuid',
            'user_id' => 'required|string|uuid',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'tax_percentage' => 'required|integer|min:0|max:100',
        ]);

        $addSalesLineRequest = new AddSalesLineRequest(
            saleId: $validated['sale_id'],
            orderLineId: $validated['order_line_id'],
            userId: $validated['user_id'],
            quantity: $validated['quantity'],
            price: $validated['price'],
            taxPercentage: $validated['tax_percentage'],
        );

        $response = $this->addSalesLine->execute($addSalesLineRequest);

        return new JsonResponse($response->toArray(), 201);
    }
}
