<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\AddSalesLine\AddSalesLine;
use App\Sales\Application\AddSalesLine\AddSalesLineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddLineController
{
    public function __construct(
        private AddSalesLine $addSalesLine,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $saleId = (string) $request->route('sale_id');

        $validator = Validator::make([
            ...$request->all(),
            'sale_id' => $saleId,
        ], [
            'sale_id' => ['required', 'string', 'uuid'],
            'order_line_id' => ['required', 'string', 'uuid'],
            'user_id' => ['required', 'string', 'uuid'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'tax_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $addSalesLineRequest = new AddSalesLineRequest(
            saleId: $validated['sale_id'],
            orderLineId: $validated['order_line_id'],
            userId: $validated['user_id'],
            quantity: $validated['quantity'],
            price: $validated['price'],
            taxPercentage: $validated['tax_percentage'],
        );

        try {
            $response = $this->addSalesLine->execute($addSalesLineRequest);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], $this->resolveStatusCodeFromInvalidArgument($exception));
        }

        return new JsonResponse($response->toArray(), 201);
    }

    private function resolveStatusCodeFromInvalidArgument(\InvalidArgumentException $exception): int
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'error, not found')) {
            return 404;
        }

        return 422;
    }
}
